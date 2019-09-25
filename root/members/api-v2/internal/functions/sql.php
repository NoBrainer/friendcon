<?php
//TODO: update documentation

/**
 * Convert $mysqli->info string format ("Rows matched: x Changed: y Warnings: z") into an array to make it more useful.
 *
 * @param $MySQLi_CON
 * @return array
 */
function mysqliInfoArray($MySQLi_CON) {
    preg_match_all('/(\S[^:]+): (\d+)/', $MySQLi_CON->info, $matches);
    return array_combine($matches[1], $matches[2]);
}

function executeSqlForAffectedRows($MySQLi_CON, $query, $types = '', ...$params) {
    $stmt = prepareSqlStatement($MySQLi_CON, $query, $types, ...$params);
    $stmt->execute();
    $affectedRows = $stmt->affected_rows;
    $stmt->close();
    return $affectedRows;
}

function executeSqlForInfo($MySQLi_CON, $query, $types = '', ...$params) {
    $stmt = prepareSqlStatement($MySQLi_CON, $query, $types, ...$params);
    $stmt->execute();
    $stmt->close();
    $info = mysqliInfoArray($MySQLi_CON);
    return [
            "matched"  => intval($info["Rows matched"]),
            "changed"  => intval($info["Changed"]),
            "warnings" => intval($info["Warnings"])
    ];
}

function executeSqlForResult($MySQLi_CON, $query, $types = '', ...$params) {
    $stmt = prepareSqlStatement($MySQLi_CON, $query, $types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}

function executeSql($MySQLi_CON, $query, $types = '', ...$params) {
    $stmt = prepareSqlStatement($MySQLi_CON, $query, $types, ...$params);
    $stmt->execute();
    $stmt->close();
}

/**
 * Create a prepared statement and bind parameters if they're provided.
 *
 * @param mysqli $MySQLi_CON - (REQUIRED) mysqli connection
 * @param string $query - (REQUIRED) mysql query string
 * @param string $types - (Default: '') A string that contains one or more characters which specify the types for the
 * corresponding bind variables: i=integer, d=double, s=string, b=blob.
 * For example: "iisd" is two integers followed by a string then a double.
 * @param mixed $params - The corresponding variables for each character in $types.
 * @return mixed
 */
function prepareSqlStatement($MySQLi_CON, $query, $types = '', ...$params) {
    $stmt = $MySQLi_CON->prepare($query);
    $stmt->bind_param($types, ...$params);
    return $stmt;
}

/**
 * Get the next row from a sql call's result.
 *
 * @param mixed $result
 * @return array
 */
function getNextRow($result = null) {
    return $result == null ? null : $result->fetch_assoc();
}

/**
 * Check if the result has 1+ rows. If a number is provided, check if the result has exactly that amount of rows.
 *
 * @param mixed $result
 * @param int $num
 * @return bool
 */
function hasRows($result = null, $num = -1) {
    if ($result == null) return false;
    return $num < 0 ? $result->num_rows > 0 : $result->num_rows == $num;
}
