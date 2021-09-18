<?php defined('BASEPATH') OR exit('No direct script access allowed');

    /**
     * Class responsible for managing the List table in the database.
     *
     * @author LanternCode <leanbox@lanterncode.com>
     * @copyright LanternCode (c) 2019
     * @version Pre-release
     * @link https://lanterncode.com/Uber-Rapsy/
     */
	class ListsModel extends CI_Model
	{
		function __construct(){
	     	parent::__construct();
	    }

        /**
         * Fetch all playlists.
         *
         * @return array      returns an array containing the lists found
         */
        function GetAllLists()
		{
            $sql = "SELECT * FROM list";
			return $this->db->query( $sql )->result();
        }

        /**
         * Fetch Ids and names of all playlists.
         *
         * @return array      returns an array containing the details found
         */
        function GetListsIdsAndNames(): array
        {
			$sql = "SELECT ListId, ListName FROM list";
			return $this->db->query( $sql )->result();
		}

        /**
         * Returns the URL of a playlist.
         *
         * @param int $listId  id of the playlist
         * @return string      returns the URL found
         */
		function GetListUrlById(int $listId): string
        {
			$sql = "SELECT ListUrl FROM list WHERE ListId = $listId";
			return $this->db->query( $sql )->row()->ListUrl;
		}

        /**
         * Inserts a new playlist into the database.
         *
         * @param array $data  details of the playlist to be inserted
         * @return boolean     true if query worked, false if it failed
         */
		function InsertPlaylist(array $data = []): bool
        {
			$link = $data['link'];
			$title = $data['title'];
			$desc = $data['description'];

			$sql = "INSERT INTO list(`ListURL`, `ListName`, `ListDesc`) VALUES ('$link', '$title', '$desc')";
			if($this->db->simple_query($sql)) return true;
            else return false;
		}
}
