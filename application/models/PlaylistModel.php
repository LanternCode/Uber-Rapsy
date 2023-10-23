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
     * @param array $data  details of the playlist to be inserted
     * @return boolean     true if query worked, false if it failed
     */
    function InsertPlaylist(array $data = []): bool
    {
        $link = $data['link'];
        $title = $data['title'];
        $desc = $data['description'];

        $sql = "INSERT INTO list(`ListURL`, `ListName`, `ListDesc`, `ListIntegrated`, `ListActive`) VALUES ('$link', '$title', '$desc', true, true)";
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
        if(isset($this->db->query($sql)->row()->ListName))
        {
            return $this->db->query($sql)->row();
        }
        else return false;
    }

    /**
     * Update the playlist's Etag once it's gone out of date
     *
     * @param int $playlistId
     * @param string $newEtag
     * @return void
     */
    function UpdatePlaylistEtag(int $playlistId, string $newEtag): void
    {
        $sql = "UPDATE list SET ListEtag = '$newEtag' WHERE ListId = $playlistId";
        $this->db->simple_query($sql);
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
     * Fetches ListId property of the playlist with matching timestamp
     *
     * @param int $timestamp  id of the playlist
     * @return int returned id
     */
    function GetPlaylistIdByTimestamp($timestamp)
    {
        $sql = "SELECT ListId FROM list WHERE ListCreatedAt = '$timestamp'";
        if(isset($this->db->query($sql)->row()->ListId))
        {
            return $this->db->query($sql)->row()->ListId;
        }
        else return 0;
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
     * There are a lot of checkboxes for each song entry, and this function makes it possible
     * to filter songs in a given playlist by specifying the checked checkbox.
     *
     * @param $listId int the id of the list to fetch the songs from
     * @param $propertyName string the name of the checkbox property to filter by
     * @return array
     */
    function FilterSongsByCheckboxProperty(int $listId, string $propertyName): array
    {
        $sql = "SELECT * FROM song WHERE $propertyName = 1 AND ListId = $listId AND SongVisible = 1";

        return $this->db->query($sql)->result();
    }
}
