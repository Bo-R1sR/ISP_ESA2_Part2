<?php
session_start();

$confirmNewTodoPage = false;
$overviewPage = false;
$confirmSavePage = false;
$logout = false;

if (isset($_POST["details"])) {
    $_SESSION["index"]=(int)$_POST["index"];
}
if (isset($_POST["next"])) {
    $_SESSION["index"] = $_SESSION["index"] + 1;
}
if (isset($_POST["prev"])) {
    $_SESSION["index"] = $_SESSION["index"] - 1;
}
if (isset($_POST["overview"])) {
    $overviewPage = true;
}
if (isset($_POST["delete"])) {
    if ($_SESSION["index"] != 0) {
        array_splice($_SESSION["dataArray"],$_SESSION["index"], 1);
        $_SESSION["index"]=$_SESSION["index"]-1;
        $_SESSION["modified"]=1;
    } else {
        $_SESSION["index"] = 0;
    }
}
if (isset($_POST["add"])) {
    $_SESSION["tempArray"] = [];
    $newEntry = array("id" => $_POST["id"], "titel" => $_POST["titel"], "kurzbeschreibung" => $_POST["kurzbeschreibung"], "vermerk" => $_POST["vermerk"], "sollDatum" => $_POST["sollDatum"]);
    $oldEntry = $_SESSION["dataArray"][$_SESSION["index"]];
    $result = array_diff($oldEntry, $newEntry);
    if(!empty($result))  {
        array_push($_SESSION["tempArray"], $newEntry);
        $confirmNewTodoPage = true;
    }  else {
        $_SESSION["index"] = 0;
    }
}
if (isset($_POST["saveFirst"])) {
    $modArray["id"] = (int)$_SESSION["tempArray"][0]["id"];
    $modArray["titel"] = $_SESSION["tempArray"][0]["titel"];
    $modArray["kurzbeschreibung"] =$_SESSION["tempArray"][0]["kurzbeschreibung"];
    $modArray["vermerk"] = $_SESSION["tempArray"][0]["vermerk"];
    $modArray["sollDatum"] = $_SESSION["tempArray"][0]["sollDatum"];
    $_SESSION["dataArray"][$_SESSION["index"]] = $modArray;
    $_SESSION["index"] = 0;
    $_SESSION["modified"]=1;
    $confirmNewTodoPage = false;
}
if (isset($_POST["continue"])) {
    $confirmNewTodoPage = false;
    // reset geht automatisch
    $_SESSION["index"] = 0;
}
if (isset($_POST["save"])) {
    if ($_SESSION["index"] == 0) {
        $lastElement = end($_SESSION["dataArray"]);
        $newItem["id"] = (int)($lastElement["id"] + 1);
        $newItem["titel"] = $_POST["titel"];
        $newItem["kurzbeschreibung"] = $_POST["kurzbeschreibung"];
        $newItem["vermerk"] = $_POST["vermerk"];
        $newItem["sollDatum"] = $_POST["sollDatum"];
        array_push($_SESSION["dataArray"], $newItem);
        $_SESSION["index"] = count($_SESSION["dataArray"]) - 1;
    } else {
        $modArray["id"] = (int)$_POST["id"];
        $modArray["titel"] = $_POST["titel"];
        $modArray["kurzbeschreibung"] = $_POST["kurzbeschreibung"];
        $modArray["vermerk"] = $_POST["vermerk"];
        $modArray["sollDatum"] = $_POST["sollDatum"];
        $_SESSION["dataArray"][$_SESSION["index"]] = $modArray;
    }
    $_SESSION["modified"]=1;
}

if (empty($_SESSION["dataArray"])) {
    $filename = "data.json";
    if (file_exists($filename)) {
        $strJsonFileContents = file_get_contents($filename);
        $_SESSION["dataArray"] = json_decode($strJsonFileContents, true);
        $_SESSION["index"] = 1;
    } else {
        $_SESSION["dataArray"] = [];
        $newEntry = array("id" => 0, "titel" => "", "kurzbeschreibung" => "", "vermerk" => "", "sollDatum" => "");
        array_push($_SESSION["dataArray"], $newEntry);
        $_SESSION["index"] = 0;
    }
}
$displayArray = $_SESSION["dataArray"][$_SESSION["index"]];
$sizeSessionArray = count($_SESSION["dataArray"]);

if (isset($_POST["destroySession"])) {
    if(isset($_SESSION["modified"]))  {
        $confirmSavePage = true;
    } else {
        session_destroy();
        $logout = true;
    }
}
if (isset($_POST["saveAll"])) {
    $jsonString = json_encode($_SESSION["dataArray"]);
    file_put_contents("data.json", $jsonString);
    session_destroy();
    $logout = true;
}
if (isset($_POST["saveNone"])) {
    session_destroy();
    $logout = true;
}

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Boris Roßbach, Kathrin Hapke">
    <title>Ausbaustufe 2</title>
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
</head>
<body  <?php if ($logout) echo "style='display:none'; ?>"  ?>   class="p-3 mb-2 bg-light text-black">
<div <?php if ($confirmSavePage || $confirmNewTodoPage || $overviewPage) echo "style='display:none'; ?>"  ?> class="container">
    <header>
        <h1 class="text-center">Ausbaustufe 2</h1>
    </header>
    <main>
        <form method="post">
            <div class="d-flex flex-row-reverse">
                <button name="add" class="btn btn-dark btn-lg">Aufgabe hinzufügen</button>
            </div>
            <input type="hidden" name="id"
                   value="<?php if (isset($displayArray["id"])) echo $displayArray["id"]; else echo "-1"; ?>">
            <div class="form-group">
                <label for="titel">Titel</label>
                <input type="text" id="titel" name="titel" placeholder="Bitte Titel eingeben..." class="form-control"
                       value="<?php if (isset($displayArray["titel"])) echo $displayArray["titel"]; ?>">
            </div>
            <div class="form-group">
                <label for="kurzbeschreibung">Kurzbeschreibung</label>
                <textarea id="kurzbeschreibung" name="kurzbeschreibung" rows="4"
                          placeholder="Bitte Kurzbeschreibung eingeben..."
                          class="form-control"><?php if (isset($displayArray["kurzbeschreibung"])) echo $displayArray["kurzbeschreibung"]; ?></textarea>
            </div>
            <div class="form-group">
                <label for="vermerk">Vermerk</label>
                <select name="vermerk" id="vermerk" class="form-control">
                    <option value="offen" <?php if (isset($displayArray["vermerk"]) && ($displayArray["vermerk"] == "offen")) echo "selected"; ?>>
                        offen
                    </option>
                    <option value="erledigt" <?php if (isset($displayArray["vermerk"]) && ($displayArray["vermerk"] == "erledigt")) echo "selected"; ?>>
                        erledigt
                    </option>
                </select>
            </div>
            <div class="form-group">
                <label for="sollDatum">Soll-Datum</label>
                <input type="date" id="sollDatum" name="sollDatum" class="form-control"
                       value="<?php if (isset($displayArray["sollDatum"])) echo $displayArray["sollDatum"]; ?>">
            </div>
            <div class="form-group">
                <label for="istDatum">Ist-Datum</label>
                <input type="date" id="istDatum" name="istDatum" value="<?php echo date('Y-m-d'); ?>"
                       class="form-control">
            </div>
            <button type="reset" class="btn btn-dark btn-sm">Daten zurücksetzen</button>
            <button name="save" class="btn btn-dark btn-sm">Aufgabe speichern</button>
            <button name="delete" class="btn btn-dark btn-sm"
                    onclick="return confirm('Wollen Sie die Aufgabe wirklich löschen?');">Aufgabe löschen
            </button>
        </form>
    </main>
    <footer class="mt-5">
        <form method="post">
            <input type="hidden" name="id"
                   value="<?php if (isset($displayArray["id"])) echo $displayArray["id"]; else echo "-1"; ?>">
            <div class="d-flex justify-content-around">
                <button name="prev" <?php if ($_SESSION["index"] <= 1) echo "disabled" ?> class="btn btn-dark btn-lg"
                        type="submit">Vorige Aufgabe
                </button>
                <button name="overview" class="btn btn-dark btn-lg">Aufgabenliste</button>
                <button name="next" <?php if ($_SESSION["index"] >= $sizeSessionArray - 1 || $_SESSION["index"] == 0) echo "disabled" ?>
                        class="btn btn-dark btn-lg"
                        type="submit">Nächste Aufgabe
                </button>
            </div>
            <div class="mt-5 d-flex flex-row-reverse">
                <button name="destroySession" class="btn btn-dark btn-lg">Session beenden</button>
            </div>
        </form>
    </footer>
</div>

<div <?php if (!$confirmNewTodoPage || $overviewPage) echo "style='display:none'; ?>" ?> class="container">
    <header>
        <h1 class="text-center">Ausbaustufe 2</h1>
    </header>
    <main class="mt-5">
        <form method="post">
            <div class="d-flex justify-content-center">
                <button name="saveFirst" class="btn-block m-2 btn btn-dark btn-lg">Änderung erst speichern</button>
                <button name="continue" class="btn-block m-2 btn btn-dark btn-lg">direkt fortsetzen</button>
            </div>

        </form>
    </main>
</div>

<div <?php if (!$confirmSavePage) echo "style='display:none'; ?>" ?> class="container">
    <header>
        <h1 class="text-center">Ausbaustufe 2</h1>
    </header>
    <main class="mt-5">
        <form method="post">
            <div class="d-flex justify-content-center">
                <button name="saveAll" class="btn-block m-2 btn btn-dark btn-lg">Alle Änderungen speichern</button>
                <button name="saveNone" class="btn-block m-2 btn btn-dark btn-lg">Alle Änderungen verwerfen</button>
            </div>

        </form>
    </main>
</div>

<div <?php if (!$overviewPage) echo "style='display:none'; ?>" ?> class="container">
    <header>
        <h1 class="text-center">Ausbaustufe 2</h1>
    </header>
    <main class="mt-5">

            <div class="d-flex justify-content-center">
                <?php
                echo "<table><tbody>";
                $loopArray = $_SESSION["dataArray"];

                $index = 0;
                foreach ($loopArray as $item) {

                    if ($item["id"] != 0) {
                        $index = $index + 1;
                        $out = $index;
                        echo "<tr><form method='post'><td>"; echo $item["titel"]; echo "</td>
                            <td>
                           
                            <input type='hidden' name='index' value='"; echo $out; echo "
                            ' />
                            <input class='btn-block m-2 btn btn-dark btn-lg' type='submit' name='details' value='Details' />
                            </td></form></tr>";
                    }
                }
                unset($value);
                echo "</tbody></table>";
                ?>

            </div>

    </main>
</div>

</body>
</html>

