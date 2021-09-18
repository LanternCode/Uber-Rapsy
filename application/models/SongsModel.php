<?php defined('BASEPATH') OR exit('No direct script access allowed');

    /**
     * Class responsible for managing the Song table in the database.
     *
     * @author LanternCode <leanbox@lanterncode.com>
     * @copyright LanternCode (c) 2019
     * @version Pre-release
     * @link https://lanterncode.com/Uber-Rapsy/
     */
	class SongsModel extends CI_Model
	{
		function __construct(){
	     	parent::__construct();
	    }

        /**
         * Fetch songs from a list filtering by title.
         *
         * @param int $ListId  id of the list to get songs from
         * @param string $Search  title filter
         * @return array      returns an array containing the songs found
         */
        function GetSongsFromList(int $ListId, string $Search = "" ): array
        {
			$searchQuery = " AND SongTitle LIKE '%$Search%'";
            $sql = "SELECT * FROM song WHERE ListId = $ListId";
			if($Search != "") $sql = $sql . $searchQuery;

			return $this->db->query($sql)->result();
        }

        /**
         * Fetch URL of every song in a playlist.
         *
         * @param int $ListId  id of the list to fetch URLs from
         * @return array      returns an array containing the URLs found
         */
		function GetURLsOfAllSongsInList(int $ListId): array
        {
			$sql = "SELECT SongURL FROM song WHERE ListId = $ListId";
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
         * Update a song with scores added by the reviewers.
         *
         * @param int $songId  id of the song to update
         * @param float $gradeAdam  grade added by Adam
         * @param float $gradeKoscielny  grade added by Koscielny
         * @return boolean           true if query worked, false if it failed
         */
		function UpdateSongWithScores(int $songId, float $gradeAdam, float $gradeKoscielny): bool
        {
			$sql = "UPDATE song SET SongGradeAdam = '$gradeAdam', SongGradeChurchie = '$gradeKoscielny' WHERE SongId = $songId";

			if($this->db->simple_query($sql)) return true;
			else return false;
		}

        /**
         * Update a moved song with the new PlaylistItemsId and PlaylistId.
         *
         * @param int $songId  id of the song to update
         * @param int $newPlaylistId  id of the playlist the song was moved to
         * @param string $newSongPlaylistItemsId  unique YT PlaylistItemsId (API item)
         * @return boolean           true if query worked, false if it failed
         */
		function UpdateSongPlaylist(int $songId, int $newPlaylistId, string $newSongPlaylistItemsId): bool
        {
            $sql = "UPDATE song SET ListId = $newPlaylistId, SongPlaylistItemsId = '$newSongPlaylistItemsId' WHERE SongId = $songId";

            if($this->db->simple_query($sql)) return true;
            else return false;
        }

        /**
         * Fetch songs of one reviewer, sorted by grade descending.
         *
         * @param int $ListId  id of the list to fetch from
         * @param string $operation  name of the reviewer
         * @return array           returns an array containing the songs found
         */
		function GetTopSongsFromList(int $ListId, string $operation): array
        {
			$orderBy = $operation == "Adam" ? "SongGradeAdam" : ($operation == "Churchie" ? "SongGradeChurchie" : "((SongGradeAdam+SongGradeChurchie)/2)");
			$sql = "SELECT * FROM song WHERE ListId = $ListId ORDER BY $orderBy DESC";
			return $this->db->query( $sql )->result();
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
            return $this->db->query( $sql )->row();
        }
}
