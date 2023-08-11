<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class responsible for managing the Song table in the database.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 */
class SongModel extends CI_Model
{
    function __construct(){
        parent::__construct();
    }

    /**
     * Fetch songs from a list filtering by title.
     *
     * @param int $listId  id of the list to get songs from
     * @param string $Search  title filter
     * @return array      returns an array containing the songs found
     */
    function GetSongsFromList(int $listId, string $Search = "" ): array
    {
        $searchQuery = " AND SongTitle LIKE '%$Search%'";
        $sql = "SELECT * FROM song WHERE ListId = $listId";
        if($Search != "") $sql = $sql . $searchQuery;

        return $this->db->query($sql)->result();
    }

    /**
     * Fetch songs filtering by title.
     *
     * @param string $Search  title filter
     * @return array      returns an array containing the songs found
     */
    function GetSongsFromSearch(string $Search = "" ): array
    {
        $sql = "SELECT * FROM song WHERE SongTitle LIKE '%$Search%'";
        return $this->db->query($sql)->result();
    }

    /**
     * Fetch URL of every song in a playlist.
     *
     * @param int $listId  id of the list to fetch URLs from
     * @return array      returns an array containing the URLs found
     */
    function GetURLsOfAllSongsInList(int $listId): array
    {
        $sql = "SELECT SongURL FROM song WHERE ListId = $listId";
        return $this->db->query( $sql )->result();
    }

    /**
     * Insert a song into our database.
     *
     * Every song fetched from our YT playlist is next fetched using YT API
     * and saved into out database, so it is never lost
     *
     * @param int $listId  id of the list the song is inserted into
     * @param string $songURL  YT url of the song (without youtu.be/)
     * @param string $songThumbnailURL  YT URL of the song's thumbnail
     * @param string $songTitle title of the song on YT
     * @param string $songPlaylistItemsId unique YT PlaylistItemsId (For API calls)
     * @return boolean           true if query worked, false if it failed
     */
    function InsertSong(int $listId, string $songURL, string $songThumbnailURL, string $songTitle, string $songPlaylistItemsId): bool
    {
        $sql = "INSERT INTO song (ListId, SongURL, SongThumbnailURL, SongTitle, SongPlaylistItemsId)VALUES('$listId', '$songURL', '$songThumbnailURL', '$songTitle', '$songPlaylistItemsId')";

        if($this->db->simple_query($sql)) return true;
        else return false;
    }

    /**
     * Inserts a new song into the database based on the complete song array
     *
     * @param array $queryData  song to be inserted
     * @return int new song's id
     */
    function InsertSongFromArray(array $queryData = []): int
    {
        if($this->db->insert('song', $queryData))
            return $this->db->conn_id->insert_id;
        else return false;
    }

    /**
     * Update song scores added by the reviewers.
     *
     * @param int $songId  id of the song to update
     * @param float|string $gradeAdam  grade added by Adam
     * @param float|string $gradeKoscielny  grade added by Koscielny
     * @return boolean           true if query worked, false otherwise
     */
    function UpdateSongScores(int $songId, mixed $gradeAdam, mixed $gradeKoscielny): bool
    {
        $sql = "UPDATE song SET SongGradeAdam = '$gradeAdam', SongGradeChurchie = '$gradeKoscielny' WHERE SongId = $songId";

        if($this->db->simple_query($sql)) return true;
        else return false;
    }

    /**
     * There are a lot of checkboxes for each song entry, and this function makes it possible
     * to update any one of them.
     *
     * @param $songId int the id of the song to update
     * @param $propertyName string the name of the checkbox property to update
     * @param $propertyValue bool the value to update the property to
     * @return bool
     */
    function UpdateSongCheckboxProperty(int $songId, string $propertyName, bool $propertyValue): bool
    {
        $propertyValue = $propertyValue != 1 ? 0 : 1;
        $sql = "UPDATE song SET $propertyName = $propertyValue WHERE SongId = $songId";

        if($this->db->simple_query($sql)) return true;
        else return false;
    }

    /**
     * The function takes a song by id and duplicates it to a given playlist
     *
     * @param $songId int id of the song to copy
     * @param $playlistId int id of the playlist to copy the song to
     * @return bool true if transaction successful, false otherwise
     */
    function CopySongToPlaylist(int $songId, int $playlistId): bool
    {
        $this->db->trans_begin();
        $this->db->query('CREATE TEMPORARY TABLE tmp SELECT * from song WHERE SongId = '.$songId);
        $this->db->query('ALTER TABLE tmp drop SongId;');
        $this->db->query('UPDATE tmp SET ListId = '.$playlistId);
        $this->db->query('INSERT INTO song SELECT 0,tmp.* FROM tmp;');
        $this->db->query('DROP TABLE tmp;');

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }
        else {
            $this->db->trans_commit();
            return true;
        }
    }

    /**
     * Update a moved song with the new PlaylistItemsId and PlaylistId.
     *
     * An integrated song is moved to a playlist integrated with YT
     *
     * @param int $songId  id of the song to update
     * @param int $newPlaylistId  id of the playlist the song was moved to
     * @param string $newSongPlaylistItemsId  unique YT PlaylistItemsId (API item)
     * @return boolean           true if query worked, false if it failed
     */
    function UpdateIntegratedSongPlaylist(int $songId, int $newPlaylistId, string $newSongPlaylistItemsId): bool
    {
        $sql = "UPDATE song SET ListId = $newPlaylistId, SongPlaylistItemsId = '$newSongPlaylistItemsId' WHERE SongId = $songId";

        if($this->db->simple_query($sql)) return true;
        else return false;
    }

    /**
     * Update a copied song with the new PlaylistItemsId.
     *
     * An integrated song is copied to a playlist integrated with YT, and the ItemsId
     * is required to later delete it from youtube (if required).
     *
     * @param int $songId  id of the song to update
     * @param string $newSongPlaylistItemsId  unique YT PlaylistItemsId (API item)
     * @return boolean           true if query worked, false if it failed
     */
    function UpdateCopiedSongItemsId(int $songId, string $newSongPlaylistItemsId): bool
    {
        $sql = "UPDATE song SET SongPlaylistItemsId = '$newSongPlaylistItemsId' WHERE SongId = $songId";

        if($this->db->simple_query($sql)) return true;
        else return false;
    }

    /**
     * Update a moved song with the new PlaylistId.
     *
     * A local song is in a playlist not integrated with a YT playlist
     *
     * @param int $songId  id of the song to update
     * @param int $newPlaylistId  id of the playlist the song was moved to
     * @return boolean           true if query worked, false if it failed
     */
    function UpdateLocalSongPlaylist(int $songId, int $newPlaylistId): bool
    {
        $sql = "UPDATE song SET ListId = $newPlaylistId WHERE SongId = $songId";

        if($this->db->simple_query($sql)) return true;
        else return false;
    }

    /**
     * Fetch songs of one reviewer, sorted by grade descending.
     *
     * @param int $listId  id of the list to fetch from
     * @param string $operation  name of the reviewer
     * @return array           returns an array containing the songs found
     */
    function GetTopSongsFromList(int $listId, string $operation): array
    {
        $orderBy = $operation == "Adam" ? "SongGradeAdam" : ($operation == "Churchie" ? "SongGradeChurchie" : "((SongGradeAdam+SongGradeChurchie)/2)");
        $sql = "SELECT * FROM song WHERE ListId = $listId ORDER BY $orderBy DESC";
        return $this->db->query($sql)->result();
    }

    /**
     * Fetch details of a song to move it between playlists.
     *
     * @param int $songId  id of the song to fetch
     * @return object      returns an object containing the details found
     */
    function GetSongDetailsForMoving(int $songId): object
    {
        $sql = "SELECT SongPlaylistItemsId, SongURL FROM song WHERE SongId = $songId";
        return $this->db->query($sql)->row();
    }

    /**
     * Fetch details of a song.
     *
     * @param int $songId  id of the song to fetch
     * @return object|bool      returns an object containing the details found or false it no object was found
     */
    function GetSongById(int $songId)
    {
        $sql = "SELECT * FROM song WHERE SongId = $songId";
        if(isset($this->db->query($sql)->row()->SongTitle))
        {
            return $this->db->query($sql)->row();
        }
        else return false;
    }

    /**
     * Deletes a song from the database.
     *
     * @param int $songId  id of the song to delete
     * @return void
     */
    function DeleteSong(int $songId)
    {
        $sql = "DELETE FROM song WHERE SongId = $songId";
        $this->db->query($sql);
    }

    /**
     * Inserts a new song review into the database
     *
     * @param array $songReview  review to be inserted
     * @return void
     */
    function InsertSongReview(array $songReview): void
    {
        $this->db->insert('review', $songReview);
    }

    /**
     * Fetches a song review
     *
     * @param int $songId  id of the song for which the review is made
     * @param int $userId  id of the user submitting the review
     * @return stdClass|bool table row with the review or false if it does not exist
     */
    function GetSongReview(int $songId, int $userId): bool|stdClass
    {
        $sql = "SELECT * FROM review WHERE reviewSongId = $songId AND reviewUserId = $userId";
        if(isset($this->db->query($sql)->row()->reviewId))
        {
            return $this->db->query($sql)->row();
        }
        else return false;
    }

    /**
     * Replaces an existing review with a new review
     *
     * @param array $songReview  playlist to be inserted
     * @return void
     */
    function UpdateReview(array $songReview): void
    {
        $this->db->replace('review', $songReview);
    }

    /**
     * Fetches songs with a filter of SongRehearsal set by the user from a given playlist
     *
     * @param bool $repeat SongRehearsal on or off
     * @param int $listId Current playlist id
     * @return Array songs returned
     */
    function FilterByRepeat(bool $repeat, int $listId): Array
    {
        $sql = "SELECT * FROM song WHERE SongRehearsal = $repeat AND ListId = $listId";
        return $this->db->query($sql)->result();
    }

    /**
     * Fetches songs that are not yet fully rated
     *
     * @param int $listId Current playlist id
     * @return Array songs returned
     */
    function FilterUnrated(int $listId): Array
    {
        $sql = "SELECT * FROM song WHERE SongGradeAdam = 0 AND SongGradeChurchie = 0 AND ListId = $listId
                     AND SongDistinction = 0 AND SongMemorial = 0 AND SongXD = 0 AND SongNotRap = 0
                     AND SongDiscomfort = 0 AND SongTop = 0 AND SongNoGrade = 0 AND SongUber = 0 AND SongBelow = 0";
        return $this->db->query($sql)->result();
    }

    /**
     * After a song is copied from a playlist to a playlist, we need to fetch it's ID in that playlist
     * This is done by this function
     *
     * @param $songName string
     * @param $playlistId int
     * @return int
     */
    function GetSongIdByNameAndPlaylist($songName, $playlistId)
    {
        $sql = "SELECT SongId FROM song WHERE SongTitle = '$songName' AND ListId = $playlistId";

        if(isset($this->db->query($sql)->row()->SongId))
        {
            return $this->db->query($sql)->row()->SongId;
        }
        else return 0;
    }
}
