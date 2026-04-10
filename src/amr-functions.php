<?php

use olcaytaner\Amr\Corpus\AmrCorpus;
use olcaytaner\Amr\Corpus\AmrSentence;
use olcaytaner\Amr\Corpus\AmrWord;

include 'base-functions.php';

class Rectangle{
    public int $x;
    public int $y;
    public int $width;
    public int $height;

    public function __construct(int $x, int $y, int $width, int $height){
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
    }
}

function amr_matches(AmrWord $currentWord, string $word, string $search_type): bool{
    switch ($search_type) {
        default:
        case "full":
            return str_ends_with($currentWord->getName(), "/" . $word) || $currentWord->getName() == $word;
        case "contains":
            return str_contains($currentWord->getName(), $word);
    }
}

function search_amr_for_word(DisplayParameter $parameter): array{
    $sentences = [];
    for ($i = 0; $i < $parameter->amrCorpus->sentenceCount(); $i++) {
        $sentence = $parameter->amrCorpus->getSentence($i);
        for ($j = 0; $j < $sentence->wordCount(); $j++) {
            $currentWord = $sentence->getWord($j);
            if ($currentWord instanceof AmrWord && amr_matches($currentWord, $parameter->word, $parameter->search_type)) {
                $sentences[] = $sentence;
                break;
            }
        }
    }
    return $sentences;
}

function getBoundingBox(AmrSentence $sentence): Rectangle{
    $result = new Rectangle(0, 0, 0, 0);
    $first = true;
    for ($i = 0; $i < $sentence->wordCount(); $i++) {
        $word = $sentence->getWord($i);
        $stringSize = (int) (7.3 * strlen($word->getName()));
        $x = $word->getPosition()->getX() - $stringSize - 5;
        $y = $word->getPosition()->getY() - 15;
        if ($first){
            $result = new Rectangle($x, $y,  2 * $stringSize + 20, 80);
            $first = false;
        } else {
            if ($x < $result->x){
                $result->width += $result->x - $x;
                $result->x = $x;
            } else {
                if ($x + 2 * $stringSize + 20 > $result->x + $result->width){
                    $result->width += $x + 2 * $stringSize + 20 - $result->x - $result->width;
                }
            }
            if ($y < $result->y){
                $result->height += $result->y - $y;
                $result->y = $y;
            } else {
                if ($y + 80 > $result->y + $result->height){
                    $result->height += $y + 80 - $result->y - $result->height;
                }
            }
        }
    }
    return $result;
}

function calculatePoints(int $x1, int $y1, int $x2, int $y2, array &$xpoints, array &$ypoints): void{
    $d = 10;
    $h = 5;
    $dx = $x2 - $x1;
    $dy = $y2 - $y1;
    $D = sqrt($dx * $dx + $dy * $dy);
    $xm = $D - $d;
    $xn = $xm;
    $ym = $h;
    $yn = -$h;
    $sin = $dy / $D;
    $cos = $dx / $D;
    $x = $xm * $cos - $ym * $sin + $x1;
    $ym = $xm * $sin + $ym * $cos + $y1;
    $xm = $x;
    $x = $xn * $cos - $yn * $sin + $x1;
    $yn = $xn * $sin + $yn * $cos + $y1;
    $xn = $x;
    $xpoints[1] = $xm;
    $xpoints[2] = $xn;
    $ypoints[1] = $ym;
    $ypoints[2] = $yn;
}

function create_amr(DisplayParameter $parameter): string{
    $display = "";
    $sentences = search_amr_for_word($parameter);
    foreach ($sentences as $sentence) {
        $display .= "<h2>" . substr($sentence->getFileName(), strrpos($sentence->getFileName(), "/") + 1) . "</h2>";
        $box = getBoundingBox($sentence);
        $display .= "<svg width=\"" . $box->x + $box->width . "\" height=\"" . $box->y + $box->height . "\">\n";
        for ($j = 0; $j < $sentence->wordCount(); $j++) {
            $currentWord = $sentence->getWord($j);
            if ($currentWord instanceof AmrWord){
                $stringSize = (int) (7.3 * strlen($currentWord->getName()));
                $display .= "<ellipse rx=\"" . ($stringSize / 2 + 10) . "\" ry=\"" . (20) . "\" cx=\"" . ($currentWord->getPosition()->getX() + 5) . "\" cy=\"" . ($currentWord->getPosition()->getY() + 25) . "\"/>\n";
                $display .= "<text x=\"" . ($currentWord->getPosition()->getX() - $stringSize / 2 + 5) . "\" y=\"" . ($currentWord->getPosition()->getY() + 20 + 10) . "\">" . $currentWord->getName() . "</text>\n";
            }
        }
        if ($sentence instanceof AmrSentence){
            for ($j = 0; $j < $sentence->connectionCount(); $j++) {
                $connection = $sentence->getConnection($j);
                $from = $connection->getFrom();
                $to = $connection->getTo();
                $with  = $connection->getWith();
                $x1 = $from->getPosition()->getX();
                $y1 = $from->getPosition()->getY() + 45;
                $x2 = $to->getPosition()->getX();
                $y2 = $to->getPosition()->getY() + 5;
                $display .= "<text x=\"" . (($x1 + $x2) / 2) . "\" y=\"" . (($y1 + $y2) / 2) . "\">" . $with . "</text>\n";
                $display .= "<line x1=\"" . $x1 . "\" y1=\"" . $y1 . "\" x2=\"" . $x2 . "\" y2=\"" . $y2 . "\"/>\n";
                $xpoints = [0, 0, 0];
                $ypoints = [0, 0, 0];
                $xpoints[0] = $x2;
                $ypoints[0] = $y2;
                calculatePoints($x1, $y1, $x2, $y2, $xpoints, $ypoints);
                $display .= "<polygon points=\"" . $xpoints[0] . "," . $ypoints[0] . " " . $xpoints[1] . "," . $ypoints[1] . " " . $xpoints[2] . "," . $ypoints[2] . "\"/>";
            }
        }
        $display .= "</svg>\n";
    }
    return $display;
}

function create_merged_amr_corpus(string $folder): AmrCorpus{
    $first = true;
    foreach (glob($folder . "/*") as $subDirectory) {
        if (is_dir($subDirectory) && !str_contains($subDirectory, ".git")) {
            if ($first){
                $corpus = new AmrCorpus($subDirectory);
                $first = false;
            } else {
                $corpus->combine(new AmrCorpus($subDirectory));
            }
        }
    }
    return $corpus;
}
