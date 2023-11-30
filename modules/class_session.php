<?php

require_once("config.php");

class Sessions
{

    protected $lifetime;
    private $pdo;

    function Sessions()
    {
        $this->lifetime = 60 * 120;

        try {
            $this->pdo = new PDO("mysql:host=" . HOST . ";dbname=" . DB, USER, PW);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }

        session_set_save_handler(
            array(&$this, "open"),
            array(&$this, "close"),
            array(&$this, "read"),
            array(&$this, "write"),
            array(&$this, "destroy"),
            array(&$this, "gc")
        );
    }

    public function open()
    {
        $this->gc(); // Moved this here to ensure it's called during every session open

        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($session_id)
    {
        $time = time();

        $sql = "SELECT data FROM sessions WHERE sid = :session_id AND sexpire > :time";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':session_id', $session_id, PDO::PARAM_STR);
        $stmt->bindParam(':time', $time, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['username'] = $record['data'];
        }

        return '';
    }

    public function write($session_id)
    {
        $time = time() + $this->lifetime;

        if (!isset($_SESSION['username'])) {
            return false;
        }

        $data = $_SESSION['username'];

        $sql = "REPLACE INTO sessions VALUES (:session_id, :data, :time)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':session_id', $session_id, PDO::PARAM_STR);
        $stmt->bindParam(':data', $data, PDO::PARAM_STR);
        $stmt->bindParam(':time', $time, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function destroy($session_id)
    {
        $sql = "DELETE FROM sessions WHERE sid = :session_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':session_id', $session_id, PDO::PARAM_STR);
        $stmt->execute();

        return true;
    }

    public function gc()
    {
        $time = time();

        $this->pdo->beginTransaction();

        $sql = "DELETE FROM sessions WHERE sexpire < :time";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':time', $time, PDO::PARAM_INT);
        $stmt->execute();

        $sql = "DELETE FROM cart WHERE sid NOT IN (SELECT sdata FROM sessions)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $this->pdo->commit();

        return true;
    }
}

?>
