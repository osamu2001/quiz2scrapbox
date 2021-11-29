<?php
require 'vendor/autoload.php';

use Youaoi\MeCab\MeCab;

MeCab::setDefaults([
    'dictionaryDir' => '/opt/homebrew/lib/mecab/dic/mecab-ipadic-neologd',
]);

$file = new SplFileObject("quiz.csv");
//$file = new SplFileObject("q.csv");
$file->setFlags(SplFileObject::READ_CSV);

$arr = [];
$pages = [];

foreach ($file as $quiz){
    $page = [];

    //問題,答え,読み,最終確認
    if ($quiz[0] === null) {
        continue;
    }
    [$q,$a,$y] = $quiz;
    if ($q === "問題") {
        continue;
    }
    $result = splitNouns($q);
//    var_dump($result);
    $page['title'] = 'Q:' . $q;
    $page['lines'] = [
        $page['title'],
        "[$a]",
        "",
    ];
    foreach ($result as $w) {
        if (strlen($w) > 0) {
            $page['lines'][] = "[$w]";
        }
    }
    $pages[] = $page;
}

function splitNouns(string $q): array {
    $result = [];
    $words = Mecab::parse($q);
    foreach ($words as $word) {
        $speech = $word->speech;
        $text = $word->text;
        if ($speech !== "名詞") {
            continue;
        }
        if (mb_strlen($text) <= 1) {
            continue;
        }
        $result[] = $text;
    }
    return $result;
}

$arr["pages"] = $pages;

echo json_encode($arr);
