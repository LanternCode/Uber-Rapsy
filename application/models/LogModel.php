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
    function CreateLog(string $entityType, int $entityId, string $description): bool
    {
        //ensure the entity type is legit
        $allowedTypes = ["playlist", "song", "user"];
        $validType = in_array($entityType, $allowedTypes);
        $actionBy = $_SESSION['userId'];

        if($validType)
        {
            $sql = "INSERT INTO log (`UserId`,`EntityType`, `EntityId`, `Description`) VALUES ($actionBy, '$entityType', $entityId, '$description')";
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
}
