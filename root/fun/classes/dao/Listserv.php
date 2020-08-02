<?php
namespace dao;

use util\General as General;
use util\Param as Param;
use util\Sql as Sql;

class Listserv {

	public const SUBSCRIBE_URL = 'https://friendcon.com/subscribe';
	public const UNSUBSCRIBE_URL = 'https://friendcon.com/unsubscribe';

	public static function add(string $email): bool {
		$affectedRows = Sql::executeSqlForAffectedRows("INSERT INTO listserv (email) VALUES (?)", 's', $email);
		return $affectedRows === 1;
	}

	public static function delete(string $email): bool {
		$affectedRows = Sql::executeSqlForAffectedRows("DELETE FROM listserv WHERE email = ?", 's', $email);
		return $affectedRows === 1;
	}

	public static function exists(string $email): bool {
		if (Param::isBlankString($email)) return false;
		$result = Sql::executeSqlForResult("SELECT * FROM listserv WHERE email = ?", 's', $email);
		return $result->num_rows > 0;
	}

	public static function getListString(): string {
		$emailStr = "";

		// Get the listserv emails
		$result = Sql::executeSqlForResult("SELECT * FROM listserv");
		if (!Sql::hasRows($result)) {
			$emailStr = "Listserv is empty.";
		} else {
			// Build the email string
			while ($row = Sql::getNextRow($result)) {
				if (!empty($emailStr)) $emailStr .= ", ";
				$emailStr .= $row['email'];
			}
			if (empty($emailStr)) {
				$emailStr = "Listserv is empty.";
			}
		}
		return $emailStr;
	}

	public static function isValidEmail(string $email): bool {
		return !preg_match('/[\s,<>()]/', $email) && strlen($email) <= 254;
	}

	public static function notifySubscribed(string $email): bool {
		$subject = "Subscribed to FriendCon Listserv!";
		$unsubscribeLink = General::linkHtml('unsubscribe', ListServ::UNSUBSCRIBE_URL);
		$lines = [
				"Thanks for subscribing! If you didn't do this, please $unsubscribeLink and/or contact admin@friendcon.com."
		];
		return General::sendEmailFromBot($email, $subject, $lines);
	}

	public static function notifyUnsubscribed(string $email): bool {
		$subject = "Unsubscribed from FriendCon Listserv";
		$resubscribeLink = General::linkHtml('resubscribe', Listserv::SUBSCRIBE_URL);
		$lines = [
				"You're now unsubscribed from FriendCon. Listservs are not for everyone, but if you ever change your mind, you can always $resubscribeLink."
		];
		return General::sendEmailFromBot($email, $subject, $lines);
	}
}
