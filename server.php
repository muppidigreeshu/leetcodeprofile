<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernames = isset($_POST['usernames']) ? $_POST['usernames'] : '';
    $usernamesList = array_map('trim', explode(',', $usernames));
    $results = [];

    foreach ($usernamesList as $username) {
        $apiUrl = "https://leetcode-stats-api.herokuapp.com/$username/";
        $response = @file_get_contents($apiUrl);

        if ($response === FALSE) {
            $results[] = [
                'Username' => $username,
                'Contest Rating' => 'N/A',
                'Global Rank' => 'N/A',
                'Problems Solved' => 'N/A',
                'Badges' => 'N/A',
                'Languages' => 'N/A',
                'Active Days' => 'N/A'
            ];
        } else {
            $data = json_decode($response, true);

            if (isset($data['status']) && $data['status'] === 'error') {
                $results[] = [
                    'Username' => $username,
                    'Contest Rating' => 'N/A',
                    'Global Rank' => 'N/A',
                    'Problems Solved' => 'N/A',
                    'Badges' => 'N/A',
                    'Languages' => 'N/A',
                    'Active Days' => 'N/A'
                ];
            } else {
                // Fetch badges and languages if they exist in the API response
                $badges = isset($data['badges']) ? implode(', ', $data['badges']) : 'N/A';
                $languages = isset($data['languages']) ? implode(', ', array_keys($data['languages'])) : 'N/A';

                $results[] = [
                    'Username' => $username,
                    'Contest Rating' => $data['acceptanceRate'] ?? 'N/A',
                    'Global Rank' => $data['ranking'] ?? 'N/A',
                    'Problems Solved' => $data['totalSolved'] ?? 'N/A',
                    'Badges' => $badges,
                    'Languages' => $languages,
                    'Active Days' => isset($data['submissionCalendar']) ? count($data['submissionCalendar']) : 'N/A'
                ];
            }
        }
    }

    $filename = 'leetcode_user_data.csv';
    $file = fopen($filename, 'w');
    fputcsv($file, ['Username', 'Contest Rating', 'Global Rank', 'Problems Solved', 'Badges', 'Languages', 'Active Days']);

    foreach ($results as $row) {
        fputcsv($file, $row);
    }

    fclose($file);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    readfile($filename);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeetCode Profile Data to CSV</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>LeetCode Profile Data to CSV</h1>
        <form method="POST">
            <label for="usernames">LeetCode Usernames (comma separated):</label>
            <textarea id="usernames" name="usernames" rows="5" cols="40" required></textarea>
            <br>
            <button type="submit">Generate CSV</button>
        </form>
    </div>
</body>
</html>