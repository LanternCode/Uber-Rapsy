<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model responsible for managing the User database table.
 *
 * @author LanternCode <leanbox@lanterncode.com>
 * @copyright LanternCode (c) 2019
 * @version Pre-release
 * @link https://lanterncode.com/Uber-Rapsy/
 */
class AccountModel extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Return the user data required for authentication.
     *
     * @param string $email
     * @return int|object returns 0 if the user was not found, or their data if found
     */
    public function getUserData(string $email): int|object
    {
        $sql = "SELECT id, username, password, role, accountLocked FROM user WHERE email = '$email'";
        $query = $this->db->query($sql);

        if (isset($query->row()->password) && $query->row()->password)
            return $query->row();
        else return 0;
    }

    /**
     * Fetch the user account status.
     *
     * @param $userId
     * @return bool
     */
    public function getUserAccountStatus($userId): bool
    {
        $sql = "SELECT accountLocked FROM user WHERE id = $userId";
        $query = $this->db->query($sql);
        return $query->row()->accountLocked;
    }

    /**
     * Insert a new user into the database.
     *
     * @param array $queryData new account details
     * @return int new user id
     */
    public function registerNewUser(array $queryData): int
    {
        $this->db->insert('user', $queryData);
        return $this->db->conn_id->insert_id;
    }

    /**
     * Check whether an account with this email address exists.
     *
     * @param string $email
     * @return bool true if no such account exists, false otherwise
     */
    public function isEmailUnique(string $email): bool
    {
        $sql = "SELECT email FROM user WHERE email = '$email'";
        $query = $this->db->query($sql);

        if (isset($query->row()->email) && $query->row()->email)
            return false;
        else
            return true;
    }

    /**
     * Generate a password reset key.
     *
     * @return string
     * @author Stack Overflow (original author unknown)
     */
    public function getToken(): string
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet .= "0123456789";
        $codeALength = strlen($codeAlphabet) - 1;

        for ($i = 0; $i < 255; ++$i)
            $token .= $codeAlphabet[rand(0, $codeALength)];

        return $token;
    }

    /**
     * Attach a password reset key to the user.
     *
     * @param string $email user's email address
     * @return string the password reset key
     */
    public function insertPasswordUpdateLink(string $email): string
    {
        $keyToInsert = $this->getToken();
        $sql = "UPDATE user SET passwordResetKey = '$keyToInsert' WHERE email = '$email'";
        $this->db->simple_query($sql);

        return $keyToInsert;
    }

    /**
     * Mail the password reset link to the user.
     *
     * @param string $email
     * @param string $resetKey
     * @return void
     */
    public function sendPasswordChangeEmail(string $email, string $resetKey): void
    {
        $resetLink = base_url('forgottenPassword/reset?qs=' . $resetKey);
        $subject = "Zresetuj hasło w RAPPAR";
        $headers = array(
            'From: "RAPPAR" <noreply@uberrapsy.pl>',
            'Reply-To: noreply@uberrapsy.pl',
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8'
        );
        $txt = "Otrzymaliśmy prośbę o zresetowanie hasła przypisanego do tego adresu email <br />
                na platformie RAPPAR. Skorzystaj z poniższego linku by zresetować swoje
                hasło lub zignoruj tę wiadomość.<br /><br />
                Zresetuj hasło: <a href='$resetLink' target='_blank'>$resetLink</a><br /><br />.";

        mail($email, $subject, $txt, implode("\r\n", $headers));
    }

    /**
     * Check whether the entered password reset key is valid.
     *
     * @param string $key
     * @return int|object id of the matching user's password reset key or 0 if no matching user was found
     */
    public function validatePasswordResetString(string $key): int|object
    {
        $sql = "SELECT id FROM user WHERE passwordResetKey = '$key'";
        $query = $this->db->query($sql);

        if (isset($query->row()->id) && $query->row()->id)
            return $query->row()->id;
        else
            return 0;
    }

    /**
     * Update a user password.
     *
     * @param string $password the plaintext password to be hashed
     * @param string $userId
     * @return void
     */
    public function updateUserPassword(string $password, string $userId): void
    {
        $newPass = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE user SET passwordResetKey = NULL, password = '$newPass' WHERE id = $userId";
        $this->db->simple_query($sql);
    }

    /**
     * Update user account status.
     *
     * @param int $accountStatus the new user account status
     * @param int $userId
     * @return void
     */
    public function updateUserAccountStatus(int $accountStatus, int $userId): void
    {
        $this->db->set('accountLocked', $accountStatus);
        $this->db->where('id', $userId);
        $this->db->update('user');
    }

    /**
     * Fetch user credentials and compare the input password with the actual password.
     * Set a user session upon a successful sign in.
     *
     * @param string $enteredPassword
     * @param string $actualPassword
     * @return bool true if the sign in was successful, false otherwise
     */
    public function signIn(string $enteredPassword, string $actualPassword): bool
    {
        if ($actualPassword && password_verify($enteredPassword, $actualPassword))
            return true;
        else return false;
    }

    /**
     * Once a user signed in, establish them a session.
     *
     * @param $userData
     * @return void
     */
    public function establishUserSession($userData): void
    {
        $_SESSION['userLoggedIn'] = 1;
        $_SESSION['userRole'] = $userData->role;
        $_SESSION['userId'] = $userData->id;
        $_SESSION['username'] = $userData->username;
    }

    /**
     * Automatically sign the user visiting the homepage if a valid login cookie exists.
     *
     * @return bool true if the sign in was successful, false otherwise
     */
    public function automaticSignIn(): bool
    {
        if (empty($_COOKIE["login"]))
            return false;

        $email = json_decode($_COOKIE["login"])->userEmail;
        $password = json_decode($_COOKIE["login"])->userPassword;

        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $accountData = $this->getUserData($email);
            if (password_verify($password, $accountData->password)) {
                $this->establishUserSession();
                return true;
            }
            else return false;
        }
        else return false;
    }

    /**
     * Fetch a user's username.
     *
     * @param int $userId
     * @return string
     */
    public function fetchUsernameById(int $userId): string
    {
        $sql = "SELECT username FROM user WHERE id = $userId";
        return $this->db->query($sql)->row()->username;
    }

    /**
     * Fetch all safe user data of every user on the platform.
     * This data is displayed in a dashboard only visible to RAPPAR staff.
     * Safe user data includes: user id in the database, username, role, accountLocked status.
     *
     * @return array
     */
    public function fetchAllSafeUserdata(): array
    {
        $sql = "SELECT id, username, role, accountLocked, userScore FROM user";
        $query = $this->db->query($sql);

        return $query->result();
    }

    /**
     * Add points to the user score.
     *
     * @param int $userId
     * @param string $actionType
     * @return void
     */
    public function addUserScore(int $userId, string $actionType): void
    {
        switch ($actionType)
        {
            case 'review':
            {
                //Add ten points for each added review
                $this->db->set('userScore', 'userScore + 10', FALSE);
                break;
            }
            case 'song':
            case 'rating':
            {
                //Add one point for each added and rated song
                $this->db->set('userScore', 'userScore + 1', FALSE);
                break;
            }
            default:
            {
                //Fallback case if no correct action was specified
                $this->db->set('userScore', 'userScore', FALSE);
                break;
            }
        }

        $this->db->where('id', $userId);
        $this->db->update('user');
    }

    /**
     * Subtracts points from the user score.
     * This is required so that users do not create and immediately delete content to increase their score.
     *
     * @param int $userId
     * @param string $actionType
     * @return void
     */
    public function subtractUserScore(int $userId, string $actionType): void
    {
        switch ($actionType)
        {
            case 'review':
            {
                //Add ten points for each added review
                $this->db->set('userScore', 'userScore - 10', FALSE);
                break;
            }
            case 'song':
            {
                //Add one point for each added song
                $this->db->set('userScore', 'userScore - 1', FALSE);
                break;
            }
            default:
            {
                //Fallback case if no correct action was specified
                $this->db->set('userScore', 'userScore', FALSE);
                break;
            }
        }

        $this->db->where('id', $userId);
        $this->db->update('user');
    }

    /**
     * Get a user profile.
     *
     * @param $userId
     * @return object|false user profile or false if not found
     */
    public function getUserProfile($userId): object|false
    {
        $sql = "SELECT username, email, role, accountLocked, userScore, createdAt FROM user WHERE id = $userId";
        $row = $this->db->query($sql)->row();
        return isset($row->username) ? $row : false;
    }

    /**
     * Get the user's position in the points table.
     * Compute the difference in scores between this and the next user.
     *
     * @param $userId
     * @return object|false the user position, points to the next position, or false if
     *  the user had no score
     */
    public function getUserPositionInRanking($userId): object|false
    {
        $sql = "WITH ranked_users AS (
                SELECT
                    id, userScore, RANK() OVER (ORDER BY userScore DESC) AS user_rank
                FROM user
                WHERE userScore >= 1
            ) SELECT
                r1.user_rank,
                r1.userScore AS my_score,
                r2.userScore AS next_score,
                r1.userScore - r2.userScore AS to_next
            FROM ranked_users r1
            LEFT JOIN ranked_users r2
              ON (
                  (r1.user_rank = 1 AND r2.user_rank = 2) OR
                  (r1.user_rank > 1 AND r2.user_rank = r1.user_rank - 1)
              )
            WHERE r1.id = ?";

        $query = $this->db->query($sql, [$userId]);
        $result = $query->row() ?? false;
        return $result;
    }

    /**
     * Return top RAPPAR contributors based on the user score.
     * Sort the leaderboard using the standard competition ranking method.
     * If there are less than 100 active contributors, show top3.
     * If there are over 100 active contributors, show top10.
     * If there are over 1000 active contributors, show top100.
     *
     * @return array
     */
    public function getTopRapparContributors(): array
    {
        //Build the base query once
        $this->db->from('user');
        $this->db->where('userScore <>', 0);

        //Count matching rows
        $count = $this->db->count_all_results();

        //Choose the limit based on the count
        $limit = ($count > 1000) ? 100 : (($count > 100) ? 10 : 3);

        //Toplist with standard-competition ranking (1224)
        $sql = "SELECT id, username, userScore, position
          FROM (
            SELECT
              `user`.id,
              `user`.username,
              `user`.userScore,
              RANK() OVER (ORDER BY `user`.userScore DESC) AS position
            FROM `user`
            WHERE `user`.userScore <> 0
          ) ranked
          WHERE position <= ?
          ORDER BY position, id
        ";

        //Run the query with the chosen limit
        $toplist = $this->db->query($sql, [$limit])->result();

        return $toplist;
    }
}