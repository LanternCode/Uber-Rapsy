<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model responsible for managing the List database table.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 */
class PlaylistModel extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Fetch a playlist.
     *
     * @param int $playlistId
     * @return object|bool playlist object or false if one was not found
     */
    public function fetchPlaylistById(int $playlistId): object|bool
    {
        $sql = "SELECT * FROM list WHERE ListId = $playlistId";
        if (isset($this->db->query($sql)->row()->ListName)) {
            return $this->db->query($sql)->row();
        }
        else return false;
    }

    /**
     * Fetch all available playlists no matter their ownership status for display
     *  in the staff administrator dashboard.
     *
     * @return array
     */
    public function getAllPlaylists(): array
    {
        $this->db->select('list.*, user.username');
        $this->db->from('list');
        $this->db->join('user', 'list.ListOwnerId = user.id');
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Fetch IDs and names of all user playlists to allow
     *  for moving and copying songs between playlists.
     *
     * @return array
     */
    public function getUserPlayistsIdsAndNames(int $userId): array
    {
        $sql = "SELECT ListId, ListName FROM list WHERE ListOwnerId = $userId";
        return $this->db->query($sql)->result();
    }

    /**
     * Fetch the playlist's ListPublic property.
     * A playlist is public if its ListPublic is set to true.
     *
     * @param int $listId
     * @return string
     */
    public function getListPublicProperty(int $listId): string
    {
        $sql = "SELECT ListPublic FROM list WHERE ListId = '$listId'";
        return $this->db->query($sql)->row()->ListPublic;
    }

    /**
     * Return a playlist URL.
     *
     * @param string $listId
     * @return string
     */
    public function getListUrlById(string $listId): string
    {
        $sql = "SELECT ListUrl FROM list WHERE ListId = '$listId'";
        return $this->db->query($sql)->row()->ListUrl ?? 0;
    }

    /**
     * Return playlist id by providing its URL.
     *
     * @param string $listUrl
     * @return int
     */
    public function getListIdByUrl(string $listUrl): int
    {
        $sql = "SELECT ListId FROM list WHERE ListUrl = '$listUrl'";
        return $this->db->query($sql)->row()->ListId;
    }

    /**
     * Create a playlist.
     *
     * @param array $queryData
     * @return int the id of the newly created playlist
     */
    public function insertPlaylist(array $queryData): int
    {
        $this->db->insert('list', $queryData);
        return $this->db->conn_id->insert_id;
    }

    /**
     * Update playlist settings.
     *
     * @param array $queryData
     * @return void
     */
    public function updatePlaylist(array $queryData): void
    {
        $this->db->replace('list', $queryData);
    }

    /**
     * Update the ListPublic property.
     *
     * @param int $playlistPublic
     * @param int $listId
     * @return void
     */
    public function setPlaylistPublicStatus(int $playlistPublic, int $listId): void
    {
        $sql = "UPDATE list SET ListPublic = $playlistPublic WHERE ListId = $listId";
        $this->db->query($sql);
    }

    /**
     * Delete a local playlist.
     * A local playlist is not integrated with a YouTube playlist.
     *
     * @param int $playlistId
     * @return void
     */
    public function deleteLocalPlaylist(int $playlistId): void
    {
        $sql = "DELETE FROM list WHERE ListId = $playlistId";
        $this->db->query($sql);
    }

    /**
     * Fetch the playlist's name.
     *
     * @param int $playlistId
     * @return string|bool playlist's name or false if the playlist was not found
     */
    public function getPlaylistNameById(int $playlistId): string|bool
    {
        $sql = "SELECT ListName FROM list WHERE ListId = $playlistId";
        return $this->db->query($sql)->row()->ListName ?? false;
    }

    /**
     * Fetch the playlist's ListIntegrated property.
     *
     * @param int $playlistId
     * @return bool
     */
    public function getPlaylistIntegratedById(int $playlistId): bool
    {
        $sql = "SELECT ListIntegrated FROM list WHERE ListId = $playlistId";
        return $this->db->query($sql)->row()->ListIntegrated ?? false;
    }

    /**
     * Update the playlist's ListIntegrated property.
     *
     * @param int $playlistId
     * @param int $updatedIntegrationStatus true if integrated, false otherwise
     * @param string $updatedLink new playlist link (optional)
     * @return bool true if the query worked, false otherwise
     */
    public function updatePlaylistIntegrationStatus(int $playlistId, int $updatedIntegrationStatus, string $updatedLink = ''): bool
    {
        $updateLink = strlen($updatedLink) > 10 ? ", ListUrl = $updatedLink" : "";
        $sql = "UPDATE list SET ListIntegrated = $updatedIntegrationStatus".$updateLink." WHERE ListId = $playlistId";
        if ($this->db->simple_query($sql))
            return true;
        else return false;
    }

    /**
     * Fetch user playlists.
     *
     * @param $userId int
     * @return array
     */
    public function fetchUserPlaylists(int $userId): array
    {
        $sql = "SELECT * FROM list WHERE ListOwnerId = $userId";
        return $this->db->query($sql)->result();
    }

    /**
     * Fetch user playlists' IDs.
     *
     * @param $userId int
     * @return array
     */
    public function fetchUserPlaylistsIDs(int $userId): array
    {
        $sql = "SELECT ListId FROM list WHERE ListOwnerId = $userId";
        return $this->db->query($sql)->result();
    }

    /**
     * Fetch RAPPAR-managed playlists to display on the homepage.
     *
     * @return array
     */
    public function fetchHomepagePlaylists(): array
    {
        $sql = "SELECT * FROM list WHERE ListOwnerId = 1 AND ListPublic = 1 AND ListActive = 1";
        return $this->db->query($sql)->result();
    }

    /**
     * Fetch the playlist owner's id.
     *
     * @param int $listId
     * @return int valid user id or 0 if the playlist has no owner
     */
    public function getListOwnerById(int $listId): int
    {
        $sql = "SELECT ListOwnerId FROM list WHERE ListId = $listId";
        return $this->db->query($sql)->row()->ListOwnerId ?? 0;
    }
}
