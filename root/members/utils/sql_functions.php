<?php
/**
 * Create a prepared statement and return the result. Equivalent to $mysqli->query() but with all of the benefits of
 * prepared statements.
 *
 * @param mysqli $MySQLi_CON - (REQUIRED) mysqli connection
 * @param string $query - (REQUIRED) mysql query string
 * @param string $types - (Default: '') A string that contains one or more characters which specify the types for the
 * corresponding bind variables: i=integer, d=double, s=string, b=blob.
 * For example: "iisd" is two integers followed by a string then a double.
 * @param array $params - (Default: []) The corresponding variables for each character in $types.
 * @return mixed
 */
function prepareSqlForResult($MySQLi_CON, $query, $types = '', $params = []) {
    $stmt = $MySQLi_CON->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}

/**
 * Create a prepared statement and bind parameters if they're provided.
 *
 * @param mysqli $MySQLi_CON - (REQUIRED) mysqli connection
 * @param string $query - (REQUIRED) mysql query string
 * @param string $types - (Default: '') A string that contains one or more characters which specify the types for the
 * corresponding bind variables: i=integer, d=double, s=string, b=blob.
 * For example: "iisd" is two integers followed by a string then a double.
 * @param array $params - (Default: []) The corresponding variables for each character in $types.
 * @return mixed
 */
function prepareSqlStatement($MySQLi_CON, $query, $types = '', $params = []) {
    $stmt = $MySQLi_CON->prepare($query);
    $stmt->bind_param($types, ...$params);
    return $stmt;
}

?>