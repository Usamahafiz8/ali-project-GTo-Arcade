<?php
$extension = ".mp3";
$f = fopen('sound.json', 'r');
$size = filesize("sound.json");
$sound = fread($f, filesize("sound.json"));
$sound_json = json_decode($sound, true);
foreach($sound_json as $key => $value)
{
    if(strpos($key, $extension) !== false)
    {
        $name = $key;
        $key_ogg = str_replace($extension, ".ogg", $name);
        echo $key_ogg."</br>";
        if(array_key_exists($key_ogg, $sound_json))
        {
            $name_ogg = $sound_json[$key_ogg];
            $name_m4a = str_replace(".ogg", $extension, $name_ogg);
            $sound_json[$name] = $name_m4a;
        }
    }
}

fclose($f);
$f = fopen('sound_result.json', 'w');
fwrite($f, json_encode($sound_json));
fclose($f);
echo "done";
?>