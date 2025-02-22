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
     * A public playlist is considered to have its ListPublic property
     * set to 1.
     *
     * @return array      returns an array containing the lists found
     */
    function GetAllPublicLists()
    {
        $sql = "SELECT * FROM list WHERE ListPublic = 1";
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
     * Fetch Ids and names of all playlists.
     *
     * @param int $listId  playlist id
     * @return string      returns the property value
     */
    function getListPublicProperty(int $listId): string
    {
        $sql = "SELECT ListPublic FROM list WHERE ListId = '$listId'";
        return $this->db->query($sql)->row()->ListPublic;
    }

    /**
     * Returns the URL of a playlist.
     *
     * @param int $listId  id of the playlist
     * @return string      returns the URL found
     */
    function GetListUrlById(int $listId): string
    {
        $sql = "SELECT ListUrl FROM list WHERE ListId = '$listId'";
        return isset($this->db->query($sql)->row()->ListUrl) ? $this->db->query($sql)->row()->ListUrl : 0;
    }

    /**
     * Returns the id of a playlist.
     *
     * @param string $listUrl  url of the playlist
     * @return int      returns the id found
     */
    function GetListIdByUrl(string $listUrl): int
    {
        $sql = "SELECT ListId FROM list WHERE ListUrl = '$listUrl'";
        return $this->db->query($sql)->row()->ListId;
    }

    /**
     * Inserts a new playlist into the database.
     *
     * @param array $queryData  playlist to be inserted
     * @return int the local db id of the inserted object
     */
    function InsertPlaylist(array $queryData = []): int
    {
        $this->db->insert('list', $queryData);
        return $this->db->conn_id->insert_id;
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
        if(isset($this->db->query($sql)->row()->ListName))
        {
            return $this->db->query($sql)->row();
        }
        else return false;
    }

    /**
     * Updates ListPublic with the value given.
     *
     * @param int $playlistPublic value to update with
     * @param int $listId playlist to update
     * @return void
     */
    function SetPlaylistPublicProperty(int $playlistPublic, int $listId)
    {
        $reverse = $playlistPublic == 1 ? 0 : 1;
        $sql = "UPDATE list SET ListPublic = $reverse WHERE ListId = $listId";
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

    /**
     * Fetches name of the playlist with matching id
     *
     * @param int $playlistId  id of the playlist
     * @return string|bool playlist the name or false if not found
     */
    function GetPlaylistNameById(int $playlistId)
    {
        $sql = "SELECT ListName FROM list WHERE ListId = $playlistId";
        if(isset($this->db->query($sql)->row()->ListName))
        {
            return $this->db->query($sql)->row()->ListName;
        }
        else return false;
    }

    /**
     * Fetches ListIntegrated property of the playlist with matching id
     *
     * @param int $playlistId  id of the playlist
     * @return bool returned property
     */
    function GetPlaylistIntegratedById(int $playlistId): bool
    {
        $sql = "SELECT ListIntegrated FROM list WHERE ListId = $playlistId";

        if(isset($this->db->query($sql)->row()->ListIntegrated))
        {
            return $this->db->query($sql)->row()->ListIntegrated;
        }
        else return false;
    }

    /**
     * Updates the playlist's integration status
     *
     * @param int $playlistId  id of the playlist
     * @param int $updatedIntegrationStatus true (1) if integrated, otherwise false (0)
     * @param string $updatedLink new playlist link (optional)
     * @return bool true if the query succeeded, false otherwise
     */
    function UpdatePlaylistIntegrationStatus(int $playlistId, int $updatedIntegrationStatus, string $updatedLink = ''): bool
    {
        $updateLink = strlen($updatedLink) > 10 ? ", ListUrl = $updatedLink" : "";
        $sql = "UPDATE list SET ListIntegrated = $updatedIntegrationStatus".$updateLink." WHERE ListId = $playlistId";
        if($this->db->simple_query($sql)) return true;
        else return false;
    }

    /**
     * Fetches the precise number of songs in a playlist
     *
     * @param int $listId id of the playlist
     * @return int
     */
    function GetPlaylistSongCount(int $listId): int
    {
        $sql = "SELECT COUNT(*) as songNumber FROM song WHERE ListId = $listId";
        if(isset($this->db->query($sql)->row()->songNumber))
        {
            return $this->db->query($sql)->row()->songNumber;
        }
        else return 0;
    }

    /**
     * Fetches all playlists owned/created by the specified user
     *
     * @param $userId int the id of the playlist owner
     * @return array
     */
    function FetchUserPlaylists(int $userId): array
    {
        $sql = "SELECT * FROM list WHERE ListOwnerId = $userId";

        return $this->db->query($sql)->result();
    }

    /**
     * Fetches IDs of playlists owned/created by the specified user
     *
     * @param $userId int the id of the playlist owner
     * @return array
     */
    function FetchUserPlaylistsIDs(int $userId): array
    {
        $sql = "SELECT ListId FROM list WHERE ListOwnerId = $userId";

        return $this->db->query($sql)->result();
    }

    /**
     * Fetches all playlists to be displayed on the homepage
     *
     * @return array
     */
    function fetchHomepagePlaylists(): array
    {
        $sql = "SELECT * FROM list WHERE ListOwnerId = 1 AND ListPublic = 1 AND ListActive = 1";

        return $this->db->query($sql)->result();
    }

    /**
     * Fetches the id the playlist owner
     *
     * @param int $listId playlist id
     * @return int valid user id or 0
     */
    function GetListOwnerById(int $listId): int
    {
        $sql = "SELECT ListOwnerId FROM list WHERE ListId = $listId";
        if(isset($this->db->query($sql)->row()->ListOwnerId))
            return $this->db->query($sql)->row()->ListOwnerId;
        else return 0;
    }
}
