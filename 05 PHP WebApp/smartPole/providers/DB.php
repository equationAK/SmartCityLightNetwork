<?php

// session_start();
require_once 'db_config.php';

function userList($list_id = 0): array
{
    global $conn;

    $query = "SELECT a.title, a.list_id, st.titleGR as status, c.name as category, c.category_id as category_id, st.id as status_id
              FROM user_lists AS b INNER JOIN list AS a ON a.list_id = b.List_id
              inner join LK_category as c on a.category_id = c.category_id
              inner join LK_status as st on a.status = st.id WHERE user_id = {$_SESSION['user_id']}";

    if ($list_id) {
        $query .= " AND a.list_id = {$list_id}";
    }
    $listResult = $conn->query($query);
    $items = [];

    if ($listResult->num_rows > 0) {
        while ($row = $listResult->fetch_assoc()) {
            $tasks = userTasks($row['list_id'], $conn);
            $listID = $row['list_id'];
            $List = [];
            foreach ($tasks as $task) {
                $List[] = $task;
            }
            $items[$listID] = [
                'list_id' => $row['list_id'],
                'title' => $row['title'],
                'status_id' => $row['status_id'],
                'status' => $row['status'],
                'category_id' => $row['category_id'],
                'category' => $row['category'],
                'list' => $List,
            ];
        }
    }
    else {
        $items["Δεν Βρέθηκαν λίστες"] = [];
    }
    return $items;
}

function userTasks($list_id, $conn): array
{
    $query = "SELECT t.task_id, t.title
              FROM task as t
              INNER JOIN list as l ON l.list_id = t.list_id
              WHERE t.list_id = {$list_id}
              ORDER BY t.title";
    $taskResult = $conn->query($query);
    $tasks = [];
    if ($taskResult->num_rows > 0) {
        while ($row = $taskResult->fetch_assoc()) {
            $tasks[] = [$row['title'], $row['task_id']];
        }
    }
    else {
        $tasks[] = ["Δεν έχουν καταχωρηθεί εργασίες στη Λίστα"];
    }
    return $tasks;
}

function teamList(): array
{
    global $conn;
    $query = "SELECT name , team_id FROM team";
    $teamResult = $conn->query($query);
    $items = [];

    if ($teamResult->num_rows > 0) {
        while ($row = $teamResult->fetch_assoc()) {
            $members = teamMembers($row['team_id'], $conn);
            $teamName = $row['name'];
            $teamMembers = [];
            foreach ($members as $member) {
                $teamMembers[] = $member;
            }
            $items[$teamName] = $teamMembers;
        }
    }
    else {
        $items["Δεν Βρέθηκαν Ομάδες"] = [];
    }
    return $items;
}

function teamMembers($team_id, $conn): array
{
    $query = "SELECT u.username
              FROM users as u
              INNER JOIN TeamMembers as t ON t.userID = u.user_id
              WHERE t.teamID = {$team_id}
              ORDER BY u.username";
    $membersResult = $conn->query($query);
    $members = [];
    if ($membersResult->num_rows > 0) {
        while ($row = $membersResult->fetch_assoc()) {
            $members[] = $row['username'];
        }
    }
    else {
        $members[] = "Δεν έχουν καταχωρηθεί μέλη στην Ομάδα";
    }
    return $members;
}

function valueList($request): array
{
    global $conn;

    if ($request == "user") {
        $query = "SELECT * from users where user_id not in (select distinct userID from TeamMembers where isActive = 1)";
    }
    elseif ($request == "team") {
        $query = "SELECT * from team";
    }
    elseif ($request == "category") {
        // dynamic category list by user role criteria
        // αν είναι διαχειριστής ομάδας θα μπορεί να έχει κατηγορία ομαδική, προσωπική
        // αν είναι απλός user θα έχει μόνο προσωπική
        // αν είναι απλός user θα έχει μόνο προσωπική λίστα
        if ($_SESSION['role_id'] == 4) {
            $query = "SELECT * FROM LK_category where hiddenForUser = 0";
        }
        else // αν είναι διαχειριστής ομάδας θα μπορεί να έχει κατηγορία ομαδική, προσωπική
        {
            $query = "SELECT * FROM LK_category";
        }
    }
    elseif ($request == "listToMember") {

        $query = " SELECT u.user_id as uid, u.username as username
                    FROM users u
                    where u.`isActive` =1";
    }
    elseif ($request == "memberToList") {
        $query = " SELECT distinct l.List_id as lid, l.title as listName
                    FROM list l
                    CROSS JOIN users u
                    LEFT JOIN (
                                select *
                                from user_lists
                                where isActive = 1
                                ) ul ON l.List_id = ul.List_id AND u.user_id = ul.user_id
                    WHERE ul.users_list_id IS NULL
                    and u.`isActive` =1";
    }
    elseif ($request == "status") {
        $query = "SELECT id, titleGR from LK_status where ScopeId=10";
    }

    $result = $conn->query($query);

    $options = []; // Initialize an empty array to hold the options

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($request == "user") {
                $options[$row['user_id']] = $row['username'];
            }
            elseif ($request == "team") {
                $options[$row['team_id']] = $row['name'];
            }
            elseif ($request == "category") {
                $options[$row['category_id']] = $row['name'];
            }
            elseif ($request == "listToMember") {
                $options[$row['uid']] = $row['username'];
            }
            elseif ($request == "memberToList") {
                $options[$row['lid']] = $row['listName'];
            }
            elseif ($request == "status") {
                $options[$row['id']] = $row['titleGR'];
            }
        }
    }
    else {
        $options[''] = 'No options found';
    }
    return $options; // Return the options array
}