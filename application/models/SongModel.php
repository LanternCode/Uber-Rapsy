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
     * Fetch songs from a playlist, filtering by the song title.
     * Only visible songs, that is, not manually hidden by the user, are returned.
     *
     * @param int $listId  id of the playlist to get songs from
     * @param string $Search  title filter
     * @return array returns an array containing the songs found
     */
    function getPlaylistSongs(int $listId, string $Search = ""): array
    {
        $sql = "SELECT * FROM playlist_song AS ps JOIN song AS s ON ps.songId = s.SongId WHERE ps.listId = $listId AND ps.SongVisible = 1 AND ps.SongDeleted = 0";
        
        //Apply the search filter
        if ($Search != "") {
            $searchQuery = " AND SongTitle LIKE '%$Search%'";
            $sql .= $searchQuery;
        }

        return $this->db->query($sql)->result();
    }

    /**
     * Fetch all songs from a list, no matter their visibility settings.
     *
     * @param int $listId  id of the list to get songs from
     * @return array      returns an array containing the songs found
     */
    function GetAllSongsFromList(int $listId): array
    {
        $sql = "SELECT * FROM song WHERE ListId = $listId";

        return $this->db->query($sql)->result();
    }

    /**
     * Fetch songs filtering by title.
     *
     * @param string $Search  title filter
     * @return array      returns an array containing found songs, if any
     */
    function GetSongsFromSearch(string $Search = "" ): array
    {
        //If logged in, search in your playlists and in public playlists
        if (isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn']){
            $ownerCondition = " AND ((l.ListPublic = true AND SongVisible = 1) OR l.ListOwnerId IN (1, ".$_SESSION['userId']."))";
        }
        else $ownerCondition = " AND ((l.ListPublic = true AND SongVisible = 1) OR l.ListOwnerId = 1)";

        //Admin staff can scan through private playlists for compliance and CS reasons
        if($this->SecurityModel->debuggingEnabled())
            $sql = "SELECT * FROM song WHERE SongTitle LIKE '%$Search%' AND SongVisible = 1";
        else $sql = "SELECT * FROM song AS s JOIN list AS l ON s.ListId = l.ListId WHERE s.SongTitle LIKE '%$Search%'".$ownerCondition;

        return $this->db->query($sql)->result();
    }

    /**
     * Fetch URL of every song in a playlist.
     *
     * @param int $listId  id of the list to fetch URLs from
     * @return array      returns an array containing the URLs found
     */
    function getURLsOfPlaylistSongs(int $listId): array
    {
        $sql = "SELECT s.SongURL FROM song AS s JOIN playlist_song AS ps ON ps.songId = s.SongId WHERE ps.listId = $listId";
        return $this->db->query($sql)->result();
    }

    /**
     * Insert a song into the local database.
     *
     * Every song fetched from our YT playlist is next fetched using YT API
     * and saved into the database, so it is never lost
     *
     * @param string $songURL  YT url of the song (without youtu.be/)
     * @param string $songThumbnailURL  YT URL of the song's thumbnail
     * @param string $songTitle title of the song on YT
     * @param string $songPlaylistItemsId unique YT PlaylistItemsId (For API calls)
     * @param string $songChannelName the name of the YT channel that uploaded the song
     * @return int id of the inserted song
     */
    function insertSong(string $songURL, string $songThumbnailURL, string $songTitle, string $songPlaylistItemsId, string $songChannelName): int
    {
        $queryData = array(
            'SongURL' => $songURL,
            'SongThumbnailURL' => $songThumbnailURL,
            'SongTitle' => $songTitle,
            'SongPlaylistItemsId' => $songPlaylistItemsId,
            'SongChannelName' => $songChannelName
        );

        $this->db->insert('song', $queryData);
        return $this->db->conn_id->insert_id;
    }

    /**
     * Insert a song into a playlist
     *
     * @param int $listId  id of the playlist the song is inserted into
     * @param int $songId  id of the song
     * @return int id of the inserted playlist_song
     */
    public function insertPlaylistSong(int $listId, int $songId): int
    {
        $queryData = array(
            'listId' => $listId,
            'songId' => $songId
        );

        $this->db->insert('playlist_song', $queryData);
        return $this->db->conn_id->insert_id;
    }

    /**
     * The method checks if the song with the selected URL, title and coming from the same channel
     *  already exists in the database. If it does, it returns its id.
     *
     * @param string $songURL  YT url of the song (without youtu.be/)
     * @param string $songTitle  YT title of the song
     * @param string $songChannelName  YT channel name that uploaded the song
     * @return int song id (or 0 if not found)
     */
    public function songExists(string $songURL, string $songTitle, string $songChannelName): int
    {
        $sql = "SELECT SongId FROM song WHERE SongURL = '$songURL' AND SongTitle = '$songTitle' AND SongChannelName = '$songChannelName'";
        return $this->db->query($sql)->row()->SongId ?? 0;
    }

    /**
     * The method checks if the song with the selected URL, title and coming from the same channel
     *  already exists in the database. If it does, it returns its id.
     *
     * @param int $listId id of the playlist the song is inserted into
     * @param int $songId id of the song
     * @return int playlist_song id (or 0 if not found)
     */
    public function playlistSongExists(int $listId, int $songId): int
    {
        $sql = "SELECT id FROM playlist_song WHERE listId = $listId AND songId = $songId";
        return $this->db->query($sql)->row()->id ?? 0;
    }

    /**
     * Update song scores added by the reviewers.
     *
     * @param int $songId  id of the song to update
     * @param float|string $gradeAdam  grade added by Adam
     * @param float|string $gradeKoscielny  grade added by Koscielny
     * @param float|string $gradeOwner  grade added by the playlist owner
     * @return boolean           true if query worked, false otherwise
     */
    function UpdateSongScores(int $songId, mixed $gradeAdam, mixed $gradeKoscielny, mixed $gradeOwner): bool
    {
        $sql = "UPDATE song SET SongGradeAdam = '$gradeAdam', SongGradeChurchie = '$gradeKoscielny', SongGradeOwner = '$gradeOwner' WHERE SongId = $songId";

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
    function updateIntegratedSongPlaylist(int $songId, int $newPlaylistId, string $newSongPlaylistItemsId): bool
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
    function updateCopiedSongItemsId(int $songId, string $newSongPlaylistItemsId): bool
    {
        $sql = "UPDATE song SET SongPlaylistItemsId = '$newSongPlaylistItemsId' WHERE SongId = $songId";

        if($this->db->simple_query($sql)) return true;
        else return false;
    }

    /**
     * This function updates the reviewers' song comment
     *
     * @param int $songId
     * @param string $songComment
     * @return bool
     */
    function UpdateSongComment(int $songId, string $songComment): bool
    {
        $sql = "UPDATE song SET SongComment = '$songComment' WHERE SongId = $songId";

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
    function updateLocalSongPlaylist(int $songId, int $newPlaylistId): bool
    {
        $sql = "UPDATE song SET ListId = $newPlaylistId WHERE SongId = $songId";

        if($this->db->simple_query($sql)) return true;
        else return false;
    }

    /**
     * Fetch songs of one reviewer, sorted by grade descending.
     *
     * @param int $listId  id of the list to fetch from
     * @param string $filter  name of the reviewer
     * @return array           returns an array containing the songs found
     */
    function GetTopSongsFromList(int $listId, string $filter): array
    {
        $cond = $filter === "Adam" ? "SongGradeAdam" : ($filter === "Churchie" ? "SongGradeChurchie" : ($filter === "Owner" ? "SongGradeOwner" : "(SongGradeAdam+SongGradeChurchie+SongGradeOwner)/3"));
        $sql = "SELECT * FROM song WHERE SongVisible = 1 AND ListId = $listId AND ".$cond." > 0 ORDER BY $cond DESC";

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
     * Marks a song as deleted from a playlist.
     *
     * @param int $songId  id of the song to delete
     * @return void
     */
    function DeleteSong(int $songId)
    {
        $sql = "UPDATE song SET SongDeleted = 1, SongVisible = 0 WHERE SongId = $songId";
        $this->db->query($sql);
    }

    /**
     * Marks a song as hidden from the playlist.
     *
     * @param int $songId  id of the song to hide
     * @param bool $newVisibility  1 to make the song visible, 0 to hide it
     * @return void
     */
    function UpdateSongVisibility(int $songId, bool $newVisibility)
    {
        $sql = "UPDATE song SET SongVisible = '$newVisibility' WHERE SongId = $songId";
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
     * Fetches playlist songs that are not yet rated
     *
     * @param int $listId Current playlist id
     * @return Array songs returned
     */
    function filterUnrated(int $listId): Array
    {
        $sql = "SELECT * FROM playlist_song WHERE SongGradeAdam = 0 AND SongGradeChurchie = 0 AND SongGradeOwner = 0
                     AND ListId = $listId AND SongVisible = 1 AND SongDeleted = 0
                     AND SongRehearsal = 0 AND SongDistinction = 0 AND SongMemorial = 0 AND SongXD = 0 AND SongNotRap = 0
                     AND SongDiscomfort = 0 AND SongTop = 0 AND SongNoGrade = 0 AND SongUber = 0 AND SongBelow = 0
                     AND SongBelTen = 0 AND SongBelNine = 0 AND SongBelEight = 0 AND SongBelFour = 0
                     AND SongDuoTen = 0 AND SongVeto = 0 AND SongBelHalfSeven = 0 AND SongBelHalfEight = 0
                     AND SongBelHalfNine = 0 AND SongDepA = 0 AND SongComment = ''";
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

    /**
     * There are a lot of checkboxes for each song entry, and this function makes it possible
     * to filter songs in a given playlist by specifying the required checked checkboxes.
     *
     * @param $listId int the id of the list to fetch the songs from
     * @param $propertyName string the name of the checkbox property to filter by
     * @return array
     */
    function filterSongsByCheckboxProperty(int $listId, string $propertyName): array
    {
        $sql = "SELECT * FROM playlist_song WHERE $propertyName = 1 AND listId = $listId AND SongVisible = 1 AND SongDeleted = 0";

        return $this->db->query($sql)->result();
    }

    /**
     * Fetches the songs to display on the song toplist
     *
     * @return array
     */
    function fetchSongsForToplist(): array
    {
        $sql = "SELECT * FROM song WHERE SongVisible = 1 AND SongId = 7998 LIMIT 1";
        return $this->db->query($sql)->result();
    }

    function addSongRating($queryData)
    {
        $this->db->insert('song_rating', $queryData);
    }

    function updateSongRating($queryData)
    {
        $result = $this->db->replace('song_rating', $queryData);
        return $result;
    }

    function checkSongRatingExists($songId, $userId): bool
    {
        $query = $this->db->get_where('song_rating', [
            'songId' => $songId,
            'userId' => $userId
        ]);

        return ($query->result_id->num_rows > 0);
    }

    function fetchSongRating($songId, $userId)
    {
        $query = $this->db->get_where('song_rating', [
            'songId' => $songId,
            'userId' => $userId
        ]);
        return ($query->row()->songGrade ?? 0);
    }

    function fetchSongAverage($songId)
    {
        $this->db->select('AVG(songGrade) as avg_rating');
        $this->db->from('song_rating');
        $this->db->where('songId', $songId);
        $query = $this->db->get();
        $result = $query->row();
        return $result->avg_rating;
    }

    function fetchSongAwards($songId)
    {
        $query = $this->db->get_where('song_award', [
            'songId' => $songId
        ]);
        return $query->result();
    }
}
