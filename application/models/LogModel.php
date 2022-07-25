<?php defined('BASEPATH') OR exit('No direct script access allowed');

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
    function CreateLog($entityType, $entityId, $description)
    {
        //ensure the entity type is legit
        $allowedTypes = ["playlist", "song", "user"];
        $validType = in_array($entityType, $allowedTypes);

        if($validType)
        {
            $sql = "INSERT INTO log (`EntityType`, `EntityId`, `Description`) VALUES ('$entityType', $entityId, '$description')";
            if($this->db->simple_query($sql)) return true;
            else return false;
        }
        else return false;
    }
}