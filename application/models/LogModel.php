<?php defined('BASEPATH') OR exit('No direct script access allowed');

if(!isset($_SESSION)){
    session_start();
}

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
     * Creates a new log in the table
     *
     * @param string $entityType  type of the entity logged (song, playlist, user)
     * @param integer $entityId   unique identifier of the entity
     * @param string $description the logged message
     * @return boolean            true if query worked, false if it failed
     */
    function CreateLog(string $entityType, int $entityId, string $description, int $reportId = 0): bool
    {
        //ensure the entity type is legit
        $allowedTypes = ["playlist", "song", "user"];
        $validType = in_array($entityType, $allowedTypes);
        $actionBy = $_SESSION['userId'] ?? 0;

        if($validType)
        {
            $sql = "INSERT INTO log (`UserId`,`EntityType`, `EntityId`, `Description`, `raportId`) VALUES ($actionBy, '$entityType', $entityId, '$description', $reportId)";
            if($this->db->simple_query($sql)) return true;
            else return false;
        }
        else return false;
    }

    /**
     * Fetches the logs for a playlist
     *
     * @param integer $playlistId   unique identifier of the playlist
     */
    function GetPlaylistLog(int $playlistId): array
    {
        $sql = "SELECT * FROM log WHERE EntityType = 'playlist' AND EntityId = $playlistId";
        return $this->db->query($sql)->result();
    }

    /**
     * Fetches the logs for a song
     *
     * @param integer $songId   unique identifier of the song
     */
    function GetSongLog(int $songId): array
    {
        $sql = "SELECT * FROM log WHERE EntityType = 'song' AND EntityId = $songId";
        return $this->db->query($sql)->result();
    }

    /**
     * Fetches a single report with the specified id number
     *
     * @param $reportId the id of the report to fetch
     * @return mixed
     */
    function FetchReport($reportId)
    {
        $sql = "SELECT * FROM report WHERE reportId = $reportId";
        return $this->db->query($sql)->row();
    }

    /**
     * This function submits the provided report and returns its id
     *
     * @param $reportText The text to report
     * @return int id of the report or 0 if submission failed
     */
    function SubmitReport($reportText)
    {
        $sql = "INSERT INTO report (`reportText`) VALUES ('$reportText')";
        if($this->db->query($sql)) return $this->db->conn_id->insert_id;
        else return 0;
    }

    /**
     * Fetches the id the report owner
     *
     * @param int $reportId report id
     * @return int valid user id or 0
     */
    function GetReportOwnerById(int $reportId): int
    {
        $sql = "SELECT UserId FROM log WHERE raportId = $reportId";
        if(isset($this->db->query($sql)->row()->UserId))
            return $this->db->query($sql)->row()->UserId;
        else return 0;
    }
}
