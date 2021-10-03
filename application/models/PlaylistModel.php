<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class responsible for managing the List table in the database.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 */
class PlaylistModel extends CI_Model
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
     * Fetch all playlists set as public.
     *
     * A public playlist is considered to have its ListActive property
     * set to 1.
     *
     * @return array      returns an array containing the lists found
     */
    function GetAllPublicLists()
    {
        $sql = "SELECT * FROM list WHERE ListActive = 1";
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

    /**
     * Inserts a new local playlist into the database.
     *
     * @param array $queryData  playlist to be inserted
     * @return void
     */
    function InsertLocalPlaylist(array $queryData = []): void
    {
        $this->db->insert('list', $queryData);
    }

    /**
     * Updates a playlist in the database.
     *
     * @param array $queryData  playlist to be updated
     * @return void
     */
    function UpdatePlaylist(array $queryData = []): void
    {
        $this->db->replace('list', $queryData);
    }

    /**
     * Fetches a playlist from the database.
     *
     * @param int $playlistId  id of the playlist to fetch
     * @return object|bool playlist object or false if not found
     */
    function FetchPlaylistById(int $playlistId)
    {
        $sql = "SELECT * FROM list WHERE ListId = $playlistId";
        if(isset($this->db->query( $sql )->row()->ListName))
        {
            return $this->db->query( $sql )->row();
        }
        else return false;
    }

    /**
     * Updates ListActive with the value given.
     *
     * @param int $playlistActive value to update with
     * @param int $listId playlist to update
     * @return void
     */
    function SetPlaylistActiveProperty(int $playlistActive, int $listId)
    {
        $reverse = $playlistActive == 1 ? 0 : 1;
        $sql = "UPDATE list SET ListActive = $reverse WHERE ListId = $listId";
        $this->db->query($sql);
    }

    /**
     * Deletes a local playlist from the database.
     *
     * @param int $playlistId  id of the playlist to delete
     * @return void
     */
    function DeleteLocalPlaylist(int $playlistId)
    {
        $sql = "DELETE FROM list WHERE ListId = $playlistId";
        $this->db->query($sql);
    }
}
