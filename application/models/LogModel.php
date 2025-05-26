<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!isset($_SESSION))
    session_start();


/**
 * Class responsible for managing the Log table in the database
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 */
class LogModel extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Creates a new log
     *
     * @param string $entityType type of the object to attach the log to (song, playlist, user, playlist_song)
     * @param int $entityId unique entity id
     * @param string $description the logged message
     * @param int $reportId if attaching a report to the log, provide the report id
     * @return bool true if query worked, false otherwise
     */
    public function createLog(string $entityType, int $entityId, string $description, int $reportId = 0): bool
    {
        //Ensure the entity type is correct
        $allowedTypes = ["playlist", "song", "user", "playlist_song"];
        $validType = in_array($entityType, $allowedTypes);
        $actionBy = $_SESSION['userId'] ?? 0;

        if ($validType) {
            $sql = "INSERT INTO log (`UserId`,`EntityType`, `EntityId`, `Description`, `reportId`) VALUES ($actionBy, '$entityType', $entityId, '$description', $reportId)";
            if ($this->db->simple_query($sql))
                return true;
            else return false;
        }
        else return false;
    }

    /**
     * Fetch playlist logs
     *
     * @param int $playlistId
     */
    function GetPlaylistLog(int $playlistId): array
    {
        $sql = "SELECT * FROM log WHERE EntityType = 'playlist' AND EntityId = $playlistId";
        return $this->db->query($sql)->result();
    }

    /**
     * Fetches playlist_song logs
     *
     * @param int $playlistSongId
     */
    public function getPlaylistSongLogs(int $playlistSongId): array
    {
        $sql = "SELECT * FROM log WHERE EntityType = 'playlist_song' AND EntityId = $playlistSongId";
        return $this->db->query($sql)->result();
    }

    /**
     * Fetches a report with the specified id number
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
     * This function submits the provided report and returns its id
     *
     * @param string $reportText
     * @return int id of the report or 0 if submission failed
     */
    function SubmitReport(string $reportText)
    {
        $sql = "INSERT INTO report (`reportText`) VALUES ('$reportText')";
        if ($this->db->query($sql))
            return $this->db->conn_id->insert_id;
        else return 0;
    }

    /**
     * Fetches the report owner's id
     *
     * @param int $reportId
     * @return int valid user id or 0
     */
    public function getReportOwnerById(int $reportId): int
    {
        $sql = "SELECT li.ListOwnerId FROM report AS r JOIN log AS l ON r.reportId = l.reportId JOIN list as li ON l.EntityId = li.ListId WHERE r.reportId = $reportId";
        if (isset($this->db->query($sql)->row()->ListOwnerId))
            return $this->db->query($sql)->row()->ListOwnerId;
        else return 0;
    }
}
