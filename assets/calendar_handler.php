<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''), '/');
$action = $_POST['action'] ?? '';
$message = '';
$messageType = 'success';

try {
    $db = db_connect();

    if ($action === 'add_event' || $action === 'edit_event') {
        $title = trim((string) ($_POST['title'] ?? ''));
        $eventDate = trim((string) ($_POST['event_date'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));

        if ($title === '') {
            throw new InvalidArgumentException('Please enter the event title.');
        }

        if ($eventDate === '') {
            throw new InvalidArgumentException('Please select the event date.');
        }

        if ($action === 'add_event') {
            db_prepare_and_execute($db, 'INSERT INTO calendar_events (event_date, title, description) VALUES (?, ?, ?)', [$eventDate, $title, $description]);
            $message = 'Event added successfully.';
        } else {
            $eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
            if ($eventId === false || $eventId <= 0) {
                throw new InvalidArgumentException('Invalid event id.');
            }
            db_prepare_and_execute($db, 'UPDATE calendar_events SET event_date = ?, title = ?, description = ? WHERE event_id = ?', [$eventDate, $title, $description, $eventId]);
            $message = 'Event updated successfully.';
        }
    } elseif ($action === 'delete_event') {
        $eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
        if ($eventId === false || $eventId <= 0) {
            throw new InvalidArgumentException('Invalid event id.');
        }
        db_prepare_and_execute($db, 'DELETE FROM calendar_events WHERE event_id = ?', [$eventId]);
        $message = 'Event deleted successfully.';
    } else {
        throw new InvalidArgumentException('Invalid action.');
    }
} catch (Throwable $e) {
    $messageType = 'error';
    $message = $e->getMessage();
}

$_SESSION['calendar_message'] = $message;
$_SESSION['calendar_message_type'] = $messageType;
header('Location: ' . $basePath . '/calendar.php');
exit;
