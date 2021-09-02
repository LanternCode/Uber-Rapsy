<?php defined('BASEPATH') OR exit('No direct script access allowed');

	class SongsModel extends CI_Model
	{
		function __construct(){
	     	parent::__construct();
	    }

        function GetSongsFromList( $ListId, $Search = "" )
		{
			$searchQuery = " AND SongTitle LIKE '%$Search%'";
            $sql = "SELECT * FROM song WHERE ListId = $ListId";
			if($Search != "") $sql = $sql . $searchQuery;
			
            $query = $this->db->query( $sql )->result();
			return $query;
        }

		function GetURLsOfAllSongsInList( $ListId )
		{
			$sql = "SELECT SongURL FROM song WHERE ListId = $ListId";
			$query = $this->db->query( $sql )->result();
			return $query;
		}

		function InsertSong($listId, $songURL, $songThumbnailURL, $songTitle)
		{
			$sql = "INSERT INTO song (ListId, SongURL, SongThumbnailURL, SongTitle)VALUES('$listId', '$songURL', '$songThumbnailURL', '$songTitle')";

			if($this->db->simple_query( $sql )) return true;
			else return false;
		}

		function UpdateSongWithScores($songId, $gradeAdam, $gradeKoscielny)
		{
			$sql = "UPDATE song SET SongGradeAdam = '$gradeAdam', SongGradeChurchie = '$gradeKoscielny' WHERE SongId = $songId";

			if($this->db->simple_query( $sql )) return true;
			else return false;
		}

		function UpdateSongPlaylist($songId, $newPlaylistId)
        {
            $sql = "UPDATE song SET ListId = $newPlaylistId WHERE SongId = $songId";

            if($this->db->simple_query( $sql )) return true;
            else return false;
        }

		function GetTopSongsFromList($ListId, $operation)
		{
			$orderBy = $operation == "Adam" ? "SongGradeAdam" : ($operation == "Churchie" ? "SongGradeChurchie" : "((SongGradeAdam+SongGradeChurchie)/2)");

			$sql = "SELECT * FROM song WHERE ListId = $ListId ORDER BY $orderBy DESC";
            $query = $this->db->query( $sql )->result();
			return $query;
		}
}
