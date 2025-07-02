<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class responsible for the Song table.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 */
class SongModel extends CI_Model
{
    function __construct()
    {
        $this->load->model('SecurityModel');
        parent::__construct();
    }

    /**
     * Returns a single song item.
     *
     * @param string $songId
     * @return object
     */
    public function getSong(string $songId): object
    {
        $sql = "SELECT * FROM song WHERE SongId = $songId";
        return $this->db->query($sql)->row();
    }

    /**
     * Insert a song into the local database.
     *
     * Every song fetched from our YT playlist is next fetched using YT API
     * and saved into the database, so it is never lost
     *
     * @param string $songURL YT url of the song (without youtu.be/). Can be left empty for manual importing.
     * @param string $userId current user inserting the song
     * @param string $songThumbnailURL YT URL of the song's thumbnail
     * @param string $songTitle title of the song on YT
     * @param string $songChannelName the name of the YT channel that uploaded the song
     * @param string $songReleaseYear song release year
     * @return int id of the inserted song
     */
    public function insertSong(string $songURL, string $userId, string $songThumbnailURL, string $songTitle, string $songChannelName, string $songReleaseYear): int
    {
        $queryData = array(
            'SongURL' => $songURL,
            'SongAddedBy' => $userId,
            'SongThumbnailURL' => $songThumbnailURL,
            'SongTitle' => $songTitle,
            'SongChannelName' => $songChannelName,
            'SongReleaseYear' => $songReleaseYear
        );

        $this->db->insert('song', $queryData);
        return $this->db->conn_id->insert_id;
    }

    /**
     * Update an existing song.
     *
     * @param array $queryData
     * @return void
     */
    public function updateSong(array $queryData): void
    {
        $this->db->replace('song', $queryData);
    }

    /**
     * The method checks if the song with the selected URL, title and coming from the same channel
     *  already exists in the database. If it does, it returns its id.
     *
     * @param string $songExternalId YT url of the song (without youtu.be/)
     * @param string $songTitle YT title of the song
     * @param string $songChannelName YT channel name that uploaded the song
     * @return int song id (or 0 if not found)
     */
    public function songExists(string $songExternalId, string $songTitle, string $songChannelName): int
    {
        $query = $this->db->select('SongId')
            ->from('song')
            ->where('SongURL', $songExternalId)
            ->get();

        if (isset($query->row()->SongId) && $query->row()->SongId > 0)
            return $query->row()->SongId;

        $query = $this->db->select('SongId')
            ->from('song')
            ->where('SongTitle', $songTitle)
            ->get();

        if (isset($query->row()->SongTitle) && isset($query->row()->SongChannelName)) {
            if ($query->row()->SongChannelName == $songChannelName)
                return $query->row()->SongId;
            else return 0;
        } else return 0;
    }

    /**
     * Adds a song rating (who and how rated what song).
     *
     * @param array $queryData
     * @return void
     */
    function addSongRating(array $queryData)
    {
        $this->db->insert('song_rating', $queryData);
    }

    /**
     * Updates an existing song rating.
     *
     * @param array $queryData
     * @return void
     */
    function updateSongRating(array $queryData)
    {
        $this->db->replace('song_rating', $queryData);
    }

    /**
     * Checks if the user already rated a song.
     *
     * @param string $songId
     * @param string $userId
     * @return bool true if rated, false otherwise
     */
    function checkSongRatingExists(string $songId, string $userId): bool
    {
        $query = $this->db->get_where('song_rating', [
            'songId' => $songId,
            'userId' => $userId
        ]);

        return ($query->result_id->num_rows > 0);
    }

    /**
     * Retrieves the logged user's song rating
     *
     * @param string $songId
     * @param string $userId
     * @return float|int song rating or 0 if unrated
     */
    public function fetchSongRating(string $songId, string $userId): float|int
    {
        $query = $this->db->get_where('song_rating', [
            'songId' => $songId,
            'userId' => $userId
        ]);
        return ($query->row()->songGrade ?? 0);
    }

    /**
     * Retrieves the average of song's scores
     *
     * @param string $songId
     * @return float
     */
    public function fetchSongAverage(string $songId): float
    {
        $this->db->select('AVG(songGrade) as avg_rating');
        $this->db->from('song_rating');
        $this->db->where('songId', $songId);
        $query = $this->db->get();
        $result = $query->row();
        return $result->avg_rating ?? 0;
    }

    /**
     * Retrieves all song's awards (award labels).
     *
     * @param string $songId
     * @return array
     */
    function fetchSongAwards(string $songId): array
    {
        $query = $this->db->get_where('song_award', [
            'songId' => $songId
        ]);
        return $query->result();
    }

    /**
     * Fetch songs, filtering by the song title.
     * Only visible songs, that is, not hidden by staff, are visible
     *
     * @param string $search title filter
     * @return array returns an array containing the songs found
     */
    public function searchSongs(string $search = ""): array
    {
        $sql = "SELECT * FROM song WHERE SongTitle LIKE '%$search%' AND SongVisible = 1 AND SongDeleted = 0";
        return $this->db->query($sql)->result();
    }

    /**
     * Fetch the top 100 songs for the Top100 Rappar Hits toplist
     *
     * @return array
     */
    function fetchTopRapparHits()
    {
        $this->db->select('song.SongId, song.SongTitle, AVG(song_rating.songGrade) as avg_rating');
        $this->db->from('song_rating');
        $this->db->join('song', 'song.SongId = song_rating.songId');
        $this->db->group_by('song_rating.songId');
        $this->db->order_by('avg_rating', 'DESC');
        $this->db->limit(100);
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Inserts a new song review
     *
     * @param array $songReview
     * @return void
     */
    public function insertSongReview(array $songReview): void
    {
        $this->db->insert('review', $songReview);
    }

    /**
     * Fetches a song review.
     *
     * @param int $songId id of the song for which the review is made
     * @param int $userId id of the user submitting the review
     * @return object|bool the review object or false if it does not exist
     */
    public function getSongReview(int $songId, int $userId): bool|object
    {
        $sql = "SELECT * FROM review WHERE reviewSongId = $songId AND reviewUserId = $userId";
        if (isset($this->db->query($sql)->row()->reviewId))
            return $this->db->query($sql)->row();
        else return false;
    }

    /**
     * Replaces an existing user review with their new review.
     *
     * @param array $songReview
     * @return void
     */
    public function updateSongReview(array $songReview): void
    {
        $this->db->replace('review', $songReview);
    }

    /**
     * Check if the song with the selected title, made by the same authors and released the same year
     *  already exists in the database. If it does, return its id.
     *
     * This method is used when inserting songs manually. Because such songs do not have YouTube IDs,
     *  their originality must be separately checked against the database.
     *
     * @param string $songTitle
     * @param string $songAuthors
     * @param string $songReleaseYear
     * @return int song id (or 0 if not found)
     */
    public function manualSongExists(string $songTitle, string $songAuthors, string $songReleaseYear): int
    {
        $query = $this->db->select('SongId')
            ->from('song')
            ->where('SongTitle', $songTitle)
            ->where('SongChannelName', $songAuthors)
            ->where('SongReleaseYear', $songReleaseYear)
            ->get();

        return $query->row()->SongId ?? 0;
    }
}