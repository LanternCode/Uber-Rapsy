<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model responsible for managing the Song database table.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 */
class SongModel extends CI_Model
{
    public function __construct()
    {
        $this->load->model('SecurityModel');
        parent::__construct();
    }

    /**
     * Return a single song item.
     *
     * @param string $songId
     * @return object|false false if the item with the provided id does not exist
     */
    public function getSong(string $songId): object|false
    {
        $sql = "SELECT * FROM song WHERE SongId = $songId";
        return $this->db->query($sql)->row() ?? false;
    }

    /**
     * Insert a song into the local database.
     * Every song fetched from a YT playlist is saved into the local database, so it is never lost.
     *
     * @param string $songURL YT url of the song (without youtu.be/). Can be left empty for manual importing.
     * @param string $userId current user inserting the song
     * @param string $songThumbnailURL YT URL of the song's thumbnail
     * @param string $songTitle song title on YT
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
     * Check if the song with the selected URL, title, and coming from the same channel
     *  already exists in the database. If it does, return its id.
     *
     * @param string $songExternalId YT url of the song (without youtu.be/)
     * @param string $songTitle song title on YT
     * @param string $songChannelName YT channel name that uploaded the song
     * @return int song id (or 0 if not found)
     */
    public function songExists(string $songExternalId, string $songTitle, string $songChannelName): int
    {
        $query = $this->db->select('SongId')
            ->from('song')
            ->where('SongURL', $songExternalId)
            ->get();

        if (!empty($query->row()->SongId))
            return $query->row()->SongId;

        $query = $this->db->select('SongId')
            ->from('song')
            ->where('SongTitle', $songTitle)
            ->get();

        if (isset($query->row()->SongChannelName)) {
            if ($query->row()->SongChannelName == $songChannelName)
                return $query->row()->SongId;
            else return 0;
        }
        else return 0;
    }

    /**
     * Add a song rating.
     * One song can be rated by any number of users.
     * A user may only rate the same song once.
     *
     * @param array $queryData
     * @return void
     */
    public function addSongRating(array $queryData): void
    {
        $this->db->insert('song_rating', $queryData);
    }

    /**
     * Update an existing song rating.
     *
     * @param array $queryData
     * @return void
     */
    public function updateSongRating(array $queryData): void
    {
        $this->db->replace('song_rating', $queryData);
    }

    /**
     * Check if the user already rated the song.
     *
     * @param string $songId
     * @param string $userId
     * @return bool true if rated, false otherwise
     */
    public function checkSongRatingExists(string $songId, string $userId): bool
    {
        $query = $this->db->get_where('song_rating', [
            'songId' => $songId,
            'userId' => $userId
        ]);

        return ($query->result_id->num_rows > 0);
    }

    /**
     * Retrieve the current user's song rating.
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
     * Retrieve the average song rating.
     *
     * @param string $songId
     * @return float
     */
    public function fetchSongAverage(string $songId): float
    {
        $sql = "
            SELECT AVG(grade) AS avg_rating
            FROM (
                SELECT songGrade AS grade
                FROM song_rating
                WHERE songId = ?
        
                UNION ALL
                SELECT SongGradeAdam
                FROM song
                WHERE SongId = ? AND SongGradeAdam <> 0
        
                UNION ALL
                SELECT SongGradeChurchie
                FROM song
                WHERE SongId = ? AND SongGradeChurchie <> 0
            ) AS all_grades
        ";

        $query = $this->db->query($sql, [$songId, $songId, $songId]);
        $row = $query->row();
        return $row->avg_rating ? number_format($row->avg_rating, 2) : 0;
    }

    /**
     * Retrieve all song's awards.
     *
     * @param string $songId
     * @return array
     */
    public function fetchSongAwards(string $songId): array
    {
        $query = $this->db->get_where('song_award', [
            'songId' => $songId
        ]);
        return $query->result();
    }

    /**
     * Insert a new song award.
     *
     * @param string $songId
     * @param string $awardName
     * @return void
     */
    public function insertSongAward(string $songId, string $awardName): void
    {
        $award = [
            'songId' => $songId,
            'award' => strtoupper($awardName)
        ];
        $this->db->insert('song_award', $award);
    }

    /**
     * Delete a song award.
     *
     * @param $songAwardId
     * @return void
     */
    public function cancelSongAward($songAwardId): void
    {
        $this->db->delete('song_award', ['id' => $songAwardId]);
    }

    /**
     * Fetch songs filtered by the song title.
     *
     * @param string $search title filter
     * @param bool $isReviewer staff can see hidden and deleted songs
     * @return array
     */
    public function searchSongs(string $search = "", bool $isReviewer = false): array
    {
        $sql = "SELECT * FROM song WHERE SongTitle LIKE '%$search%'";
        $sql .= !$isReviewer ? ' AND SongVisible = 1 AND SongDeleted = 0' : '';
        return $this->db->query($sql)->result();
    }

    /**
     * Fetch the 100 highest-rated public songs.
     *
     * @return array
     */
    public function fetchTopRapparHits(): array
    {
        $sql = "
            SELECT  s.SongId,
                    s.SongTitle,
                    AVG(g.grade) AS avg_rating
            FROM song AS s
            LEFT JOIN (
                SELECT sr.songId, sr.songGrade AS grade
                FROM song_rating AS sr
            
                UNION ALL
                SELECT s2.SongId, s2.SongGradeAdam AS grade
                FROM song AS s2
                WHERE s2.SongGradeAdam IS NOT NULL AND s2.SongGradeAdam <> 0
            
                UNION ALL
                SELECT s3.SongId, s3.SongGradeChurchie AS grade
                FROM song AS s3
                WHERE s3.SongGradeChurchie IS NOT NULL AND s3.SongGradeChurchie <> 0
            ) AS g
              ON g.songId = s.SongId
            WHERE s.SongVisible = 1
              AND s.SongDeleted = 0
            GROUP BY s.SongId, s.SongTitle
            HAVING COUNT(g.grade) > 0
            ORDER BY avg_rating DESC
            LIMIT 100
        ";

        $query = $this->db->query($sql);
        return $query->result();
    }

    /**
     * Insert a new song review and return its id.
     *
     * @param array $songReview
     * @return int
     */
    public function insertSongReview(array $songReview): int
    {
        $this->db->insert('review', $songReview);
        return $this->db->conn_id->insert_id;
    }

    /**
     * Check if the user already reviewed a particular song.
     *
     * @param int $songId
     * @param int $userId
     * @return int|false song review id or false if the user did not review said song
     */
    public function checkIfUserReviewedSong(int $songId, int $userId): int|false
    {
        $sql = "SELECT reviewId FROM review WHERE reviewSongId = $songId AND reviewUserId = $userId";
        return $this->db->query($sql)->row()->reviewId ?? false;
    }

    /**
     * Return the number of public song reviews.
     * Exclude reviews made by the current user if one is logged in.
     *
     * @param int $songId
     * @param int $userId 0 means no user is logged in
     * @return int
     */
    public function getSongReviewCount(int $songId, int $userId = 0): int
    {
        $this->db->where('reviewSongId', $songId);
        $this->db->where('reviewActive', true);
        if ($userId > 0)
            $this->db->where('reviewUserId !=', $userId);
        $query = $this->db->get('review');
        return $query->num_rows();
    }

    /**
     * Fetch ten most recent public song reviews.
     * Exclude reviews made by the current user if one is logged in.
     *
     * @param int $songId
     * @param int $userId 0 means no user is logged in
     * @return array
     */
    public function fetchRecentSongReviews(int $songId, int $userId = 0): array
    {
        $this->db->join('user', 'review.reviewUserId = user.id');
        $this->db->select('review.*, user.username');
        $this->db->where('reviewSongId', $songId);
        $this->db->where('reviewActive', true);
        if ($userId > 0)
            $this->db->where('reviewUserId !=', $userId);
        $this->db->order_by('reviewInsertDate', 'DESC');
        $this->db->limit(10);
        $query = $this->db->get('review');
        return $query->result();
    }

    /**
     * Fetch a song review.
     * Also attach the reviewer's username if one is required.
     * Usernames are required for some review listings.
     *
     * @param int $reviewId
     * @param bool $includeUsername
     * @return object|false
     */
    public function getSongReview(int $reviewId, bool $includeUsername = false): object|false
    {
        if ($includeUsername) {
            $this->db->join('user', 'review.reviewUserId = user.id');
            $this->db->select('review.*, user.username');
        }
        $this->db->where('reviewId', $reviewId);
        $query = $this->db->get('review');
        return $query->row() ?? false;
    }

    /**
     * Replace an existing song review.
     *
     * @param array $songReview
     * @return void
     */
    public function updateSongReview(array $songReview): void
    {
        $this->db->replace('review', $songReview);
    }

    /**
     * Delete a song review.
     *
     * @param int $reviewId
     * @return void
     */
    public function deleteSongReview(int $reviewId): void
    {
        $this->db->where('reviewId', $reviewId);
        $this->db->delete('review');
    }

    /**
     * Check if the song with the selected title, made by the same authors and released the same year
     *  already exists in the database. If it does, return its id.
     * This is done when inserting songs manually, because such songs do not have YouTube IDs.
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

    /**
     * Update the song's visibility.
     *
     * @param int $songId
     * @param bool $newVisibility 1 to make the song visible, 0 to hide it
     * @return void
     */
    public function updateSongVisibility(int $songId, bool $newVisibility): void
    {
        $sql = "UPDATE song SET SongVisible = '$newVisibility' WHERE SongId = $songId";
        $this->db->simple_query($sql);
    }

    /**
     * Delete a song. A deleted song is marked as deleted and cannot be viewed by the users.
     * The reviewers see the entry as deleted and some of its data is retained so it is not added again.
     *
     * @param int $songId
     * @return void
     */
    public function deleteSong(int $songId): void
    {
        $sql = "UPDATE song SET SongDeleted = true WHERE SongId = $songId";
        $this->db->simple_query($sql);
    }

    /**
     * Check whether a song is active on RAPPAR. Deleted songs are considered inactive.
     *
     * @param int $songId
     * @return bool
     */
    public function isSongActive(int $songId): bool
    {
        $sql = "SELECT SongDeleted FROM song WHERE SongId = $songId";
        $songDeleted = $this->db->query($sql)->row()->SongDeleted ?? true;
        return !$songDeleted;
    }

    public function updateReviewerRatings(int $songId, float $songGradeAdam, float $songGradeChurchie): void
    {
        $sql = "UPDATE song SET SongGradeAdam = $songGradeAdam, SongGradeChurchie = $songGradeChurchie WHERE SongId = $songId";
        $this->db->simple_query($sql);
    }
}