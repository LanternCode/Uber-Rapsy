<?php defined('BASEPATH') OR exit('No direct script access allowed');

	class ListsModel extends CI_Model
	{
		function __construct(){
	     	parent::__construct();
	    }

        function GetAllLists()
		{
            $sql = "SELECT * FROM list";
            $query = $this->db->query( $sql )->result();
			return $query;
        }

        function GetListsIdsAndNames()
		{
			$sql = "SELECT ListId, ListName FROM list";
			$query = $this->db->query( $sql )->result();
			return $query;
		}

		function getListUrlById($listId)
		{
			$sql = "SELECT ListUrl FROM list WHERE ListId = $listId";
			$query = $this->db->query( $sql )->row()->ListUrl;
			return $query;
		}

		function insertPlaylist($data = [])
		{
			$link = $data['link'];
			$title = $data['title'];
			$desc = $data['description'];

			$sql = "INSERT INTO list(`ListURL`, `ListName`, `ListDesc`) VALUES ('$link', '$title', '$desc')";
			$this->db->simple_query($sql);
		}
}
