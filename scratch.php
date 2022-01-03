<?php
function delimitArray($path, $array) {

    $path = explode('.', $path);
    $num_args = count($path);

    $val = $array;
    for ( $i = 0; $i < $num_args; $i++ ) {
        // every iteration brings us closer to the truth
        $val = $val[$path[$i]];
    }
    return $val;
}

$temp = ['language' => ['code' => 'nl', 'title' => 'Netherlands']];
$address = "language.title";
echo delimitArray($address, $temp);
?>
