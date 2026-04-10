<?php

use olcaytaner\AnnotatedSentence\AnnotatedSentence;
use olcaytaner\AnnotatedSentence\AnnotatedWord;

include 'base-functions.php';
include 'search-functions.php';

class Point{
    public float $x;
    public float $y;

    public function __construct(int $x, int $y){
        $this->x = $x;
        $this->y = $y;
    }
}
function create_ud_table(DisplayParameter $parameter): string{
    $sentences = search_corpus_for_word($parameter);
    if (count($sentences) === 0) {
        return "";
    }
    $display = "<h1>" . $parameter->corpusName ."</h1>";
    foreach ($sentences as $sentence) {
        if ($sentence instanceof AnnotatedSentence){
            $display .= "<h2>" . substr($sentence->getFileName(), strrpos($sentence->getFileName(), "/") + 1) . "</h2>";
            $maxSize = 50;
            $wordSpace = 80;
            $currentLeft = $wordSpace;
            $lineSpace = 120;
            $display .= "<svg width=\"" . (80 + 130 * $sentence->wordCount()) . "\" height=\"400\">";
            $wordSize = [];
            $wordTotal = [];
            for ($j = 0; $j < $sentence->wordCount(); $j++) {
                $wordTotal[] = $currentLeft;
                $wordSize[] = $maxSize;
                $currentLeft += $maxSize + $wordSpace;
            }
            $currentLeft = $wordSpace;
            for ($j = 0; $j < $sentence->wordCount(); $j++){
                $word = $sentence->getWord($j);
                if ($word instanceof AnnotatedWord){
                    $display .= "<text x=\"" . $currentLeft . "\" y=\"" . $lineSpace . "\">" . $word->getName() . "</text>";
                    if ($word->getUniversalDependencyPos() != null){
                        $display .= "<text fill=\"red\"  x=\"" . $currentLeft . "\" y=\"" . ($lineSpace + 30) . "\">" . $word->getUniversalDependencyPos() . "</text>";
                        if ($word->getParse() != null){
                            $features = $word->getParse()->getUniversalDependencyFeatures($word->getUniversalDependencyPos());
                            for ($k = 0; $k < count($features); $k++){
                                $display .= "<text fill=\"blue\" x=\"" . $currentLeft . "\" y=\"" . ($lineSpace + 30 * ($k + 2)) . "\">" . $features[$k] . "</text>";
                            }
                        }
                    }
                    if ($word->getUniversalDependency() != null){
                        $correct = strtolower($word->getUniversalDependency()->__toString());
                        if ($word->getUniversalDependency()->to() != 0){
                            $color = "black";
                            switch ($correct){
                                case "acl":
                                    $color = "#ffd600";
                                    break;
                                case "advcl":
                                    $color = "#00796b";
                                    break;
                                case "aux":
                                    $color = "#000080";
                                    break;
                                case "advmod":
                                    $color = "#1e88d5";
                                    break;
                                case "amod":
                                    $color = "#b71c1c";
                                    break;
                                case "det":
                                    $color = "#ff80ab";
                                    break;
                                case "flat":
                                    $color = "#6a1b9a";
                                    break;
                                case "obj":
                                    $color = "#43a047";
                                    break;
                                case "conj":
                                    $color = "#afb42b";
                                    break;
                                case "mark":
                                    $color = "#ff6f00";
                                    break;
                                case "nmod":
                                    $color = "#ff8a65";
                                    break;
                                case "nsubj":
                                    $color = "#b39ddb";
                                    break;
                                case "obl":
                                    $color = "#87cefa";
                                    break;
                                case "compound":
                                    $color = "#546e7a";
                                    break;
                                case "cc":
                                    $color = "#795548";
                                    break;
                                case "ccomp":
                                    $color = "#cd5c5c";
                                    break;
                                case "case":
                                    $color = "#bc8f8f";
                                    break;
                                case "nummod":
                                    $color = "#8fbc8f";
                                    break;
                                case "xcomp":
                                    $color = "#d2691d";
                                    break;
                                case "parataxis":
                                    $color = "#5c6bc0";
                                    break;
                            }
                            $startX = $currentLeft + $maxSize / 2;
                            $startY = $lineSpace;
                            $height = pow(abs($word->getUniversalDependency()->to() - 1 - $j), 0.7);
                            $toX = $wordTotal[$word->getUniversalDependency()->to() - 1] + $wordSize[$word->getUniversalDependency()->to() - 1] / 2;
                            $pointEnd = new Point($startX + 5 * (($word->getUniversalDependency()->to() - 1 - $j) <=> 0) + 30, $startY);
                            $pointStart = new Point($toX - 30, $startY);
                            $controlY = (int) ($pointStart->y - 20 - 20 * $height);
                            $display .= "<text fill=\"" . $color . "\" x=\"" . (((int) ($pointStart->x + $pointEnd->x) / 2) - 25) . "\" y=\"" . (int) ($controlY + 4 * $height) . "\">" . $correct . "</text>";
                            $pointCtrl1 = new Point($pointStart->x, $controlY);
                            $pointCtrl2 = new Point($pointEnd->x, $controlY);
                            $display .= "<path fill=\"none\" style=\"stroke:" . $color . ";stroke-width:2\" d=\"M" . $pointStart->x . "," . $pointStart->y . " C" . $pointCtrl1->x . "," . $pointCtrl1->y . " " . $pointCtrl2->x . "," . $pointCtrl2->y . " " . $pointEnd->x . "," . $pointEnd->y . "\" />";
                            $display .= "<line x1=\"" . (int)$pointEnd->x . "\" y1=\"" . (int)$pointEnd->y . "\" x2=\"" . ((int)$pointEnd->x - 5) . "\" y2=\"" . ((int)$pointEnd->y - 5) . "\"/>";
                            $display .= "<line x1=\"" . (int)$pointEnd->x . "\" y1=\"" . (int)$pointEnd->y . "\" x2=\"" . ((int)$pointEnd->x + 5) . "\" y2=\"" . ((int)$pointEnd->y - 5) . "\"/>";
                        } else {
                            $display .= "<text fill=\"" . "black" . "\" x=\"" . ($currentLeft + $maxSize / 2 - 10) . "\" y=\"" . ($lineSpace - 50) . "\">" . "root" . "</text>";
                            $display .= "<line x1=\"" . ($currentLeft + $maxSize / 2) . "\" y1=\"" . ($lineSpace - 40) . "\" x2=\"" . ($currentLeft + $maxSize / 2) . "\" y2=\"" . ($lineSpace - 10) . "\"/>";
                        }
                    }
                }
                $currentLeft += $maxSize + $wordSpace;
            }
            $display .= "</svg>";
        }
    }
    return $display;
}
