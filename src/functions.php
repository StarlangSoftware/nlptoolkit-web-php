<?php

use olcaytaner\Framenet\Frame;
use olcaytaner\Framenet\FrameNet;
use olcaytaner\WordNet\SynSet;
use olcaytaner\WordNet\WordNet;

function frame_list_contains(array $frames, Frame $frame): bool
{
    foreach ($frames as $current) {
        if ($current->getName() === $frame->getName()){
            return true;
        }
    }
    return false;
}

function get_frames_for_synsets(FrameNet $frameNet, array $synsets): array
{
    $result = [];
    foreach ($synsets as $synset) {
        $current = $frameNet->getFrames($synset->getId());
        foreach ($current as $frame) {
            if (!frame_list_contains($result, $frame)){
                $result[] = ($frame);
            }
        }
    }
    return $result;
}

function create_table_of_frames(WordNet $turkishWordNet, array $frames): string
{
    $display = "<table> <tr> <th>Frame</th> <th>Lexical Units</th> <th>Frame Elements</th> </tr>";
    foreach ($frames as $frame) {
        $display .= "<tr><td>" . $frame->getName() . "</td><td><table> <tr> <th>Id</th> <th>Words</th> <th>Definition</th> </tr>";
        $display = display_lexical_units($turkishWordNet, $display, $frame) . "</table></td><td>";
        for ($j = 0; $j < $frame->frameElementSize(); $j++) {
            $frameElement = $frame->getFrameElement($j);
            $display .= " " . $frameElement;
        }
        $display .= "</td></tr>";
    }
    $display .= "</table>";
    return $display;
}

function create_synonym(string $display, int $j, SynSet $synset): string
{
    $t = 0;
    for ($k = 0; $k < $synset->getSynonym()->literalSize(); $k++) {
        if ($k !== $j) {
            if ($t === 0) {
                $display .= $synset->getSynonym()->getLiteral($k)->getName();
            } else {
                $display .= "; " . $synset->getSynonym()->getLiteral($k)->getName();
            }
            $t++;
        }
    }
    return $display;
}

function display_lexical_units(WordNet $turkishWordNet, string $display, Frame $frame): string
{
    for ($j = 0;  $j < $frame->lexicalUnitSize(); $j++) {
        $lexicalUnit = $frame->getLexicalUnit($j);
        $synset = $turkishWordNet->getSynSetWithId($lexicalUnit);
        if ($synset != null) {
            $display .= "<tr><td>" . $synset->getId() . "</td><td>";
            $display = create_synonym($display, -1, $synset) . "</td><td>" . $synset->getDefinition() . "</td></tr>";
        }
    }
    return $display;
}

function create_frame_table(WordNet $turkishWordNet, FrameNet $frameNet, string $frameName): string
{
    $display = "Lexical Units <br> <table> <tr> <th>Id</th> <th>Words</th> <th>Definition</th> </tr>";
    for ($i = 0; $i < $frameNet->size(); $i++) {
        $frame = $frameNet->getFrame($i);
        if ($frame->getName() === $frameName) {
            $display = display_lexical_units($turkishWordNet, $display, $frame);
            break;
        }
    }
    $display .= "</table> <br>";
    $display .= "Frame Elements <br> <table> <th>Element</th> </tr>";
    for ($i = 0; $i < $frameNet->size(); $i++) {
        $frame = $frameNet->getFrame($i);
        if ($frame->getName() === $frameName) {
            for ($j = 0; $j < $frame->frameElementSize(); $j++) {
                $frameElement = $frame->getFrameElement($j);
                $display .= "<tr><td>" . $frameElement . "</td></tr>";
            }
            break;
        }
    }
    $display .= "</table>";
    return $display;
}
?>