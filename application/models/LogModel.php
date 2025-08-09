<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!isset($_SESSION))
    session_start();

/**
 * Model responsible for managing the Log database table.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 */
class LogModel extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create a log.
     *
     * @param string $entityType type of the object to attach the log to (song, playlist, user, playlist_song)
     * @param int $entityId unique entity id
     * @param string $description the logged message
     * @param int $reportId provide the report id if attaching a report to the log
     * @return bool true if query worked, false otherwise
     */
    public function createLog(string $entityType, int $entityId, string $description, int $reportId = 0): bool
    {
        //Ensure the entity type is correct
        $allowedTypes = ["playlist", "song", "user", "playlist_song"];
        $validType = in_array($entityType, $allowedTypes);
        $actionedBy = $_SESSION['userId'] ?? 0;

        if ($validType) {
            $sql = "INSERT INTO log (`UserId`,`EntityType`, `EntityId`, `Description`, `reportId`) VALUES ($actionedBy, '$entityType', $entityId, '$description', $reportId)";
            if ($this->db->simple_query($sql))
                return true;
            else return false;
        }
        else return false;
    }

    /**
     * Fetch playlist logs.
     *
     * @param int $playlistId
     * @return array
     */
    public function getPlaylistLog(int $playlistId): array
    {
        $sql = "SELECT * FROM log WHERE EntityType = 'playlist' AND EntityId = $playlistId";
        return $this->db->query($sql)->result();
    }

    /**
     * Fetch playlist_song logs.
     *
     * @param int $playlistSongId
     * @return array
     */
    public function getPlaylistSongLogs(int $playlistSongId): array
    {
        $sql = "SELECT * FROM log WHERE EntityType = 'playlist_song' AND EntityId = $playlistSongId";
        return $this->db->query($sql)->result();
    }

    /**
     * Fetch song logs.
     *
     * @param int $songId
     * @return array
     */
    public function getSongLogs(int $songId): array
    {
        $sql = "SELECT * FROM log WHERE EntityType = 'song' AND EntityId = $songId";
        return $this->db->query($sql)->result();
    }

    /**
     * Fetch user logs.
     *
     * @param int $userId
     * @return array
     */
    public function getUserLogs(int $userId): array
    {
        $sql = "SELECT * FROM log WHERE EntityType = 'user' AND EntityId = $userId";
        return $this->db->query($sql)->result();
    }

    /**
     * Fetch a report.
     *
     * @param string $reportId
     * @return object
     */
    public function fetchReport(string $reportId): object
    {
        $sql = "SELECT * FROM report WHERE reportId = $reportId";
        return $this->db->query($sql)->row();
    }

    /**
     * Submit a report and return its id.
     *
     * @param string $reportText
     * @return int
     */
    public function submitReport(string $reportText): int
    {
        $data = array('reportText' => $reportText);
        $this->db->insert('report', $data);
        return $this->db->insert_id();
    }

    /**
     * Fetch a report owner's id.
     *
     * @param int $reportId
     * @return int valid user id or 0 if no owner was found
     */
    public function getReportOwnerById(int $reportId): int
    {
        $sql = "SELECT li.ListOwnerId FROM report AS r JOIN log AS l ON r.reportId = l.reportId JOIN list as li ON l.EntityId = li.ListId WHERE r.reportId = $reportId";
        if (isset($this->db->query($sql)->row()->ListOwnerId))
            return $this->db->query($sql)->row()->ListOwnerId;
        else return 0;
    }
}