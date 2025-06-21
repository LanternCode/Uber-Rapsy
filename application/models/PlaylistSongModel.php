<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model responsible for managing the Playlist_Song database table.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 */
class PlaylistSongModel extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Return a single playlist_song item.
     *
     * @param string $playlistSongId
     * @return object
     */
    public function getPlaylistSong(string $playlistSongId): object
    {
        $sql = "SELECT * FROM playlist_song WHERE id = $playlistSongId";
        return $this->db->query($sql)->row();
    }

    /**
     * Fetch songs from a playlist, filtering by the song title.
     * Only visible songs, that is, not manually hidden by the user, are returned.
     *
     * @param int $listId
     * @param string $search title filter
     * @return array
     */
    public function getPlaylistSongs(int $listId, string $search = ""): array
    {
        $sql = "SELECT ps.*, s.SongId, s.SongURL, s.SongThumbnailURL, s.SongTitle  
                FROM playlist_song AS ps JOIN song AS s ON s.SongId = ps.songId 
                WHERE ps.listId = $listId AND ps.SongVisible = 1 AND ps.SongDeleted = 0";

        //Apply the search filter
        if ($search != "") {
            $searchQuery = " AND s.SongTitle LIKE '%$search%'";
            $sql .= $searchQuery;
        }

        return $this->db->query($sql)->result();
    }

    /**
     * Fetch all playlists' songs, filtering by the title.
     *
     * @param string $search title filter
     * @return array
     */
    public function getPlaylistSongsFromSearch(string $search = ""): array
    {
        //Define the search query
        $sql = "SELECT ps.*, s.SongId, s.SongURL, s.SongThumbnailURL, s.SongTitle 
                    FROM playlist_song AS ps JOIN song AS s ON s.SongId = ps.songId 
                    JOIN list AS l ON ps.listId = l.ListId 
                    WHERE s.SongTitle LIKE '%$search%'";

        //If logged in, search both in your playlists and in public playlists
        if (isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn']) {
            $ownerCondition = " AND ((l.ListPublic = 1 AND ps.SongVisible = 1 AND ps.SongDeleted = 0) OR l.ListOwnerId IN (1, " . $_SESSION['userId'] . "))";
        }
        else $ownerCondition = " AND ((l.ListPublic = 1 AND ps.SongVisible = 1 AND ps.SongDeleted = 0) OR l.ListOwnerId = 1)";

        //Admin staff can scan through private playlists for compliance and to provide CS
        if (!$this->SecurityModel->debuggingEnabled())
            $sql .= $ownerCondition;

        return $this->db->query($sql)->result();
    }

    /**
     * Fetch songs of one reviewer, sorted by grade, descending.
     *
     * @param int $listId
     * @param string $filter reviewer's name (Adam/Churchie/Owner)
     * @return array
     */
    public function getTopPlaylistSongs(int $listId, string $filter): array
    {
        $cond = $filter === "Adam" ? "ps.SongGradeAdam" : ($filter === "Churchie" ? "ps.SongGradeChurchie" : ($filter === "Owner" ? "ps.SongGradeOwner" : "(ps.SongGradeAdam+ps.SongGradeChurchie+ps.SongGradeOwner)/3"));
        $sql = "SELECT ps.*, s.SongURL, s.SongThumbnailURL, s.SongTitle 
                FROM playlist_song AS ps JOIN song AS s ON s.SongId = ps.songId 
                WHERE ps.SongVisible = 1 AND ps.SongDeleted = 0 AND ps.listId = $listId AND " . $cond . " > 0 ORDER BY $cond DESC";

        return $this->db->query($sql)->result();
    }

    /**
     * Insert a song into a playlist.
     *
     * @param int $listId
     * @param int $songId
     * @param string $songPlaylistItemsId unique YT PlaylistItemsId (For API calls)
     * @return int id of the inserted playlist_song
     */
    public function insertPlaylistSong(int $listId, int $songId, string $songPlaylistItemsId): int
    {
        $queryData = array(
            'listId' => $listId,
            'songId' => $songId,
            'SongPlaylistItemsId' => $songPlaylistItemsId,
        );

        $this->db->insert('playlist_song', $queryData);
        return $this->db->conn_id->insert_id;
    }

    /**
     * Check if the song already belongs to the playlist. If it does, return its id.
     *
     * @param int $listId
     * @param int $songId
     * @return int playlist_song id (or 0 if not found)
     */
    public function playlistSongExists(int $listId, int $songId): int
    {
        $sql = "SELECT id FROM playlist_song WHERE listId = $listId AND songId = $songId";
        return $this->db->query($sql)->row()->id ?? 0;
    }

    /**
     * Update reviewers' playlist_song scores.
     *
     * @param int $playlistSongId
     * @param float|string $gradeAdam Adam's grade
     * @param float|string $gradeKoscielny Kościelny's grade
     * @param float|string $gradeOwner Playlist owner's grade
     * @return bool true if the query worked, false otherwise
     */
    public function updatePlaylistSongScores(int $playlistSongId, mixed $gradeAdam, mixed $gradeKoscielny, mixed $gradeOwner): bool
    {
        $sql = "UPDATE playlist_song SET SongGradeAdam = '$gradeAdam', SongGradeChurchie = '$gradeKoscielny', SongGradeOwner = '$gradeOwner' WHERE id = $playlistSongId";

        if ($this->db->simple_query($sql))
            return true;
        else return false;
    }

    /**
     * There are lots of checkboxes for each playlist_song entry. Choose one and update it.
     *
     * @param $playlistSongId int
     * @param $propertyName string the database name of the checkbox property to update
     * @param $propertyValue bool the value to update the property to
     * @return bool
     */
    public function updateSongCheckboxProperty(int $playlistSongId, string $propertyName, bool $propertyValue): bool
    {
        $propertyValue = $propertyValue != 1 ? 0 : 1;
        $sql = "UPDATE playlist_song SET $propertyName = $propertyValue WHERE id = $playlistSongId";

        if ($this->db->simple_query($sql))
            return true;
        else return false;
    }

    /**
     * Duplicate a playlist_song to another playlist.
     *
     * @param $playlistSongId int
     * @param $playlistId int id of the playlist to copy the song to
     * @return false|int duplicated item's id if the transaction worked, false otherwise
     */
    public function copyToAnotherPlaylist(int $playlistSongId, int $playlistId): false|int
    {
        //Fetch the playlist_song
        $this->db->trans_start();
        $row = $this->db->where('id', $playlistSongId)->get('playlist_song')->row_array();
        if (!$row) {
            $this->db->trans_rollback();
            return false;
        }

        //Update the listId and reset the primary key
        unset($row['id']);
        $row['listId'] = $playlistId;

        //Insert the duplicate playlist_song
        $this->db->insert('playlist_song', $row);

        //Fetch the new item's id and complete the transaction
        $newId = $this->db->insert_id();
        $this->db->trans_complete();

        return $this->db->trans_status() ? $newId : false;
    }

    /**
     * Update a playlist_song with new PlaylistItemsId and PlaylistId after moving.
     * An integrated playlist_song is moved to a playlist that's integrated with YT.
     *
     * @param int $playlistSongId
     * @param int $newPlaylistId id of the playlist the playlist_song was moved to
     * @param string $newSongPlaylistItemsId unique YT PlaylistItemsId (API item)
     * @return bool true if the query worked, false otherwise
     */
    public function updateIntegratedSongPlaylistId(int $playlistSongId, int $newPlaylistId, string $newSongPlaylistItemsId): bool
    {
        $sql = "UPDATE playlist_song SET listId = $newPlaylistId, SongPlaylistItemsId = '$newSongPlaylistItemsId' WHERE id = $playlistSongId";

        if ($this->db->simple_query($sql))
            return true;
        else return false;
    }

    /**
     * Provide a copied playlist_song with a new PlaylistItemsId.
     *
     * An integrated playlist_song is copied to a playlist integrated with YT, and the ItemsId
     * is required to delete it from youtube (once the user decides to do so).
     *
     * @param int $playlistSongId
     * @param string $newSongPlaylistItemsId unique YT PlaylistItemsId (API item)
     * @return bool true if query worked, false otherwise
     */
    public function updateCopiedSongItemsId(int $playlistSongId, string $newSongPlaylistItemsId): bool
    {
        $sql = "UPDATE playlist_song SET SongPlaylistItemsId = '$newSongPlaylistItemsId' WHERE id = $playlistSongId";

        if ($this->db->simple_query($sql))
            return true;
        else return false;
    }

    /**
     * This function updates the reviewers' song comment
     *
     * @param int $playlistSongId
     * @param string $songComment
     * @return bool
     */
    function updateSongComment(int $playlistSongId, string $songComment): bool
    {
        $sql = "UPDATE playlist_song SET SongComment = '$songComment' WHERE id = $playlistSongId";

        if ($this->db->simple_query($sql))
            return true;
        else return false;
    }

    /**
     * Update a playlist_song with the new PlaylistId after moving.
     * A local song is in a playlist not integrated with a YT playlist.
     *
     * @param int $playlistSongId id of the playlist_song to update
     * @param int $newPlaylistId id of the playlist the playlist_song was moved to
     * @return bool true if query worked, false otherwise
     */
    public function updateLocalSongPlaylistId(int $playlistSongId, int $newPlaylistId): bool
    {
        $sql = "UPDATE playlist_song SET listId = $newPlaylistId WHERE id = $playlistSongId";

        if ($this->db->simple_query($sql)) return true;
        else return false;
    }

    /**
     * Flags a playlist song as deleted.
     * Original song details are retained to ensure it is not added back.
     * These are not visible to anyone. Only the playlist owner sees the title.
     *
     * @param int $playlistSongId id of the song to delete
     * @return void
     */
    function deletePlaylistSong(int $playlistSongId)
    {
        $sql = "UPDATE playlist_song SET SongDeleted = 1, SongVisible = 0 WHERE id = $playlistSongId";
        $this->db->query($sql);
    }

    /**
     * Updates the playlist song's visibility status.
     *
     * @param int $playlistSongId id of the playlist song to hide
     * @param bool $newVisibility 1 to make the song visible, 0 to hide it
     * @return void
     */
    function updatePlaylistSongVisibility(int $playlistSongId, bool $newVisibility)
    {
        $sql = "UPDATE playlist_song SET SongVisible = '$newVisibility' WHERE id = $playlistSongId";
        $this->db->query($sql);
    }

    /**
     * Fetches playlist songs that are not yet rated
     *
     * @param int $listId Current playlist id
     * @return Array songs returned
     */
    function filterUnrated(int $listId): array
    {
        $sql = "SELECT ps.*, s.SongId, s.SongURL, s.SongThumbnailURL, s.SongTitle 
                FROM playlist_song AS ps JOIN song AS s ON s.SongId = ps.songId 
                WHERE ps.SongGradeAdam = 0 AND ps.SongGradeChurchie = 0 AND ps.SongGradeOwner = 0
                     AND ps.listId = $listId AND ps.SongVisible = 1 AND ps.SongDeleted = 0
                     AND ps.SongRehearsal = 0 AND ps.SongDistinction = 0 AND ps.SongMemorial = 0 AND ps.SongXD = 0 AND ps.SongNotRap = 0
                     AND ps.SongDiscomfort = 0 AND ps.SongTop = 0 AND ps.SongNoGrade = 0 AND ps.SongUber = 0 AND ps.SongBelow = 0
                     AND ps.SongBelTen = 0 AND ps.SongBelNine = 0 AND ps.SongBelEight = 0 AND ps.SongBelFour = 0
                     AND ps.SongDuoTen = 0 AND ps.SongVeto = 0 AND ps.SongBelHalfSeven = 0 AND ps.SongBelHalfEight = 0
                     AND ps.SongBelHalfNine = 0 AND ps.SongDepA = 0 AND ps.SongComment = ''";

        return $this->db->query($sql)->result();
    }

    /**
     * There are lots of checkboxes for each song entry. This method allows the user
     *  to filter songs in playlist by specifying the required checkboxes.
     *
     * @param $listId int playlist id
     * @param $propertyName string the database name of the checkbox property to filter by
     * @return array songs found
     */
    function filterSongsByCheckboxProperty(int $listId, string $propertyName): array
    {
        $sql = "SELECT ps.*, s.SongId, s.SongURL, s.SongThumbnailURL, s.SongTitle 
                FROM playlist_song AS ps JOIN song AS s ON s.SongId = ps.songId 
                WHERE ps.$propertyName = 1 AND ps.listId = $listId AND ps.SongVisible = 1 AND ps.SongDeleted = 0";

        return $this->db->query($sql)->result();
    }
}
