<?php
    include_once("database.php"); // Defines $db, which is a mysqli connection to my student myisam database

    
    function add_video($db, $table_name, $name, $category, $length) {
        $query = "INSERT INTO `$table_name` (`name`, `category`, `length`) VALUES ('$name', '$category', '$length');";
        $res = $db->query($query);
        if (!$res) die($db->error);
    }
    
    function delete_video($db, $table_name, $id) {
        $query = "DELETE FROM `$table_name` WHERE id = $id LIMIT 1";
        $res = $db->query($query);
        if (!$res) die($db->error);
    }
    
    function delete_videos($db, $table_name) {
        $query = "DELETE FROM `$table_name`";
        $res = $db->query($query);
        if (!$res) die($db->error);
    }

    function toggle_rented($db, $table_name, $id) {
        $query = "UPDATE `$table_name` SET rented = !rented WHERE id = $id";
        $res = $db->query($query);
        if (!$res) die($db->error);
    }
    
    function fetch_videos($db, $table_name, $category) {
        if ($category and strcmp($category, "") != 0) {
            $query = "SELECT * FROM `$table_name` WHERE category = '$category'";
        } else {
            $query = "SELECT * FROM `$table_name`";
        }
        $res = $db->query($query);
        if (!$res) die($db->error);
        for ($videos = array(); $row = $res->fetch_assoc(); $videos[] = $row); // http://php.net/manual/en/mysqli-result.fetch-assoc.php
        return $videos;
    }
    
    function fetch_unique_categories($db, $table_name){ 
        $query = "SELECT DISTINCT category FROM `$table_name`";
        $res = $db->query($query);
        if (!$res) die($db->error);
        for ($videos = array(); $row = $res->fetch_assoc(); $videos[] = $row["category"]); // http://php.net/manual/en/mysqli-result.fetch-assoc.php
        return $videos;
    }
    
    function fetch_unique_video_names($db, $table_name) {
        $query = "SELECT DISTINCT name FROM `$table_name`";
        $res = $db->query($query);
        if (!$res) die($db->error);
        for ($videos = array(); $row = $res->fetch_assoc(); $videos[] = $row["name"]); // http://php.net/manual/en/mysqli-result.fetch-assoc.php
        return $videos;
    }
    
    var_dump($_POST);
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $action = $_POST["action"];
        switch ($action) {
            case "add_video":
                add_video($db, $table_name, $_POST["name"], $_POST["category"], $_POST["length"]);
                break;
            case "delete_videos":
                delete_videos($db, $table_name);
                break;
            case "delete_video":
                delete_video($db, $table_name, $_POST["id"]);
                break;
            case "toggle_status":
                toggle_rented($db, $table_name, $_POST["id"]);
                break;
        }
    }
        
        
    $videos = fetch_videos($db, $table_name, array_key_exists("category", $_GET) ? $_GET["category"] : "");
    
?>

<!DOCTYPE html>
<html>
    <head>
        <title>PHP Assignment 2</title>
    </head>
    <body>
    
        <form action="<?php echo $_SERVER["REQUEST_URI"] ?>" method="post">
            <input id="add_video" type="text" name="name" placeholder="Video Name" autofocus required pattern=".{0,255}" title="Name cannot be longer than 255 characters" />
            <input type="text" name="category" placeholder="Category" pattern=".{0,255}" title="Category cannot be longer than 255 characters" />
            <input type="number" min="0" name="length" placeholder="Length" title="Length of video must be positive" />
            <button value="add_video" name="action">Add Video</button>
            <button value="delete_videos" name="action" formnovalidate>Delete All Videos</button>
        </form>
        
        <form method="get">
            Display Only Category: 
            <select name="category" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach (fetch_unique_categories($db, $table_name) as $category) { 
                    if ($category != "") { ?>
                        <option value="<?php echo $category;?>" <?php echo array_key_exists("category", $_GET) && strcmp($_GET["category"], $category)==0 ? "selected" : "";?>><?php echo $category;?></option>
                    <?php } 
                } ?>
            </select>
        </form>
    
        <table>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Length</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php foreach ($videos as $video) { ?>
                <tr>
                    <td><?php echo $video["name"]; ?></td>
                    <td><?php echo $video["category"]; ?></td>
                    <td><?php echo $video["length"]; ?></td>
                    <td><?php echo $video["rented"] ? "checked out" : "available";?></td>
                    <td>
                        <form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post">
                            <button name="action" value="delete_video">Delete</button>
                            <button name="action" value="toggle_status">Toggle Status</button>
                            <input type="hidden" name="id" value="<?php echo $video["id"]; ?>" />
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <script>
            document.getElementById("add_video").addEventListener("input", function(input) {
                input = input.srcElement;
                error = false;
                <?php foreach (fetch_unique_video_names($db, $table_name) as $video_name) { ?>
                    if (input.value == "<?php echo $video_name; ?>") {
                        input.setCustomValidity("Video name must be unique: <?php echo $video_name;?> already exists.");
                        error = true;
                    }
                <?php } ?>
                if (!error) {
                    input.setCustomValidity("");
                }
            });    
        </script>
    </body>
</html>














