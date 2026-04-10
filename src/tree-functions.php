<?php

include 'base-functions.php';

use olcaytaner\AnnotatedSentence\ViewLayerType;
use olcaytaner\AnnotatedTree\ParseNodeDrawable;
use olcaytaner\AnnotatedTree\Processor\Condition\IsTurkishLeafNode;
use olcaytaner\AnnotatedTree\Processor\NodeDrawableCollector;
use olcaytaner\ParseTree\NodeCollector;
use olcaytaner\ParseTree\NodeCondition\IsEnglishLeaf;
use olcaytaner\ParseTree\ParseNode;

function english_tree_matches(ParseNode $currentNode, string $word, string $search_type): bool{
    switch ($search_type) {
        default:
        case "full":
            return $currentNode->getData()->getName() == $word;
        case "contains":
            return str_contains($currentNode->getData()->getName(), $word);
    }
}

function turkish_tree_matches(ParseNodeDrawable $currentNode, string $word, string $search_type): bool{
    switch ($search_type) {
        default:
        case "full":
            return $currentNode->getLayerData(ViewLayerType::TURKISH_WORD) == $word;
        case "contains":
            return str_contains($currentNode->getLayerData(ViewLayerType::TURKISH_WORD), $word);
    }
}

function search_english_treebank_for_word(DisplayParameter $parameter): array{
    $sentences = [];
    for ($i = 0; $i < $parameter->treebank->size(); $i++) {
        $parseTree = $parameter->treebank->get($i);
        $collector = new NodeCollector($parseTree->getRoot(), new IsEnglishLeaf());
        $leafList = $collector->collect();
        foreach ($leafList as $leaf){
            if (english_tree_matches($leaf, $parameter->word, $parameter->search_type)) {
                $sentences[] = $parseTree;
                break;
            }
        }
    }
    return $sentences;
}

function search_turkish_treebank_for_word(DisplayParameter $parameter): array{
    $sentences = [];
    for ($i = 0; $i < $parameter->treebankdrawable->size(); $i++) {
        $parseTree = $parameter->treebankdrawable->get($i);
        $collector = new NodeDrawableCollector($parseTree->getRoot(), new IsTurkishLeafNode());
        $leafList = $collector->collect();
        foreach ($leafList as $leaf){
            if (turkish_tree_matches($leaf, $parameter->word, $parameter->search_type)) {
                $sentences[] = $parseTree;
                break;
            }
        }
    }
    return $sentences;
}

function create_parse_tree(DisplayParameter $parameter): string{
    if ($parameter->columnWise) {
        return create_vertical_parse_tree($parameter);
    } else {
        return create_horizontal_parse_tree($parameter);
    }
}

function display_english_node_contents(DisplayParameter $parameter, ParseNode $node, int $x, int $y, string $s): string{
    if ($node->numberOfChildren() == 0 && english_tree_matches($node, $parameter->word, $parameter->search_type)) {
        return "<text x=\"" . $x . "\" y=\"" . $y . "\" fill=\"" . $parameter->color . "\">" . $s . "</text>\n";
    } else {
        return "<text x=\"" . $x . "\" y=\"" . $y . "\">" . $s . "</text>\n";
    }
}

function display_turkish_node_contents(DisplayParameter $parameter, ParseNodeDrawable $node, int $x, int $y, string $s): string{
    if ($node->numberOfChildren() == 0 && turkish_tree_matches($node, $parameter->word, $parameter->search_type)) {
        return "<text x=\"" . $x . "\" y=\"" . $y . "\" fill=\"" . $parameter->color . "\">" . $s . "</text>\n";
    } else {
        return "<text x=\"" . $x . "\" y=\"" . $y . "\">" . $s . "</text>\n";
    }
}

function get_node_contents(DisplayParameter $parameter, ParseNode $node){
    if ($node->numberOfChildren() == 0 && $parameter->treebankdrawable != null && $node instanceof ParseNodeDrawable){
        switch ($parameter->layer) {
            default:
            case "text":
                return $node->getLayerData(ViewLayerType::TURKISH_WORD);
            case "morphology":
                if ($node->layerExists(ViewLayerType::INFLECTIONAL_GROUP)){
                    return $node->getLayerData(ViewLayerType::INFLECTIONAL_GROUP);
                } else {
                    return $node->getLayerData(ViewLayerType::TURKISH_WORD);
                }
            case "metamorpheme":
                if ($node->layerExists(ViewLayerType::META_MORPHEME)){
                    return $node->getLayerData(ViewLayerType::META_MORPHEME);
                } else {
                    return $node->getLayerData(ViewLayerType::TURKISH_WORD);
                }
            case "semantics":
                if ($node->layerExists(ViewLayerType::SEMANTICS)){
                    return $node->getLayerData(ViewLayerType::TURKISH_WORD) . " " . $node->getLayerData(ViewLayerType::SEMANTICS);
                } else {
                    return $node->getLayerData(ViewLayerType::TURKISH_WORD);
                }
            case "namedentity":
                if ($node->layerExists(ViewLayerType::NER)){
                    return $node->getLayerData(ViewLayerType::TURKISH_WORD) . " " . $node->getLayerData(ViewLayerType::NER);
                } else {
                    return $node->getLayerData(ViewLayerType::TURKISH_WORD);
                }
            case "propbank":
                if ($node->layerExists(ViewLayerType::PROPBANK)){
                    return $node->getLayerData(ViewLayerType::TURKISH_WORD) . " " . $node->getLayerData(ViewLayerType::PROPBANK);
                } else {
                    return $node->getLayerData(ViewLayerType::TURKISH_WORD);
                }
        }
    } else {
        return $node->getData()->getName();
    }
}


function create_vertical_parse_node(DisplayParameter $parameter, ParseNode $node, int $maxDepth, int $nodeWidth, int $nodeHeight): string
{
    $display = "";
    $s = get_node_contents($parameter, $node);
    if ($node->getDepth() == 0){
        $addY = 15;
    } else {
        if ($node->getDepth() == $maxDepth){
            $addY = -5;
        } else {
            $addY = 5;
        }
    }
    $x = ($node->getInOrderTraversalIndex() + 1) * $nodeWidth - 20 / 2;
    $y = $node->getDepth() * $nodeHeight + $addY;
    if ($parameter->treebankdrawable == null){
        $display .= display_english_node_contents($parameter, $node, $x, $y, $s);
    } else {
        $display .= display_turkish_node_contents($parameter, $node, $x, $y, $s);
    }
    for ($i = 0; $i < $node->numberOfChildren(); $i++){
        $child = $node->getChild($i);
        $display .= "<line x1=\"" . (($node->getInOrderTraversalIndex() + 1) * $nodeWidth) . "\" y1=\"" . ($node->getDepth() * $nodeHeight + 20) . "\" x2=\"" . (($child->getInOrderTraversalIndex() + 1) * $nodeWidth) . "\" y2=\"" . ($child->getDepth() * $nodeHeight - 20) . "\"/>\n";
        $display .= create_vertical_parse_node($parameter, $child, $maxDepth, $nodeWidth, $nodeHeight);
    }
    return $display;
}

function create_vertical_parse_tree(DisplayParameter $parameter): string{
    $nodeWidth = 70;
    $nodeHeight = 80;
    if ($parameter->treebankdrawable == null){
        $trees = search_english_treebank_for_word($parameter);
    } else {
        $trees = search_turkish_treebank_for_word($parameter);
    }
    $display = "";
    foreach ($trees as $parseTree) {
        $display .= "<h3>" . substr($parseTree->getName(), strrpos($parseTree->getName(), "/") + 1) . "</h3>\n";
        $display .= "<svg width=\"" . (($parseTree->getMaxInOrderTraversalIndex() + 2) * $nodeWidth) . "\" height=\"" . (($parseTree->maxDepth() + 1) * $nodeHeight) . "\">";
        $display .= create_vertical_parse_node($parameter, $parseTree->getRoot(), $parseTree->maxDepth(), $nodeWidth, $nodeHeight);
        $display .= "</svg>\n";
    }
    return $display;
}

function create_horizontal_parse_node(DisplayParameter $parameter, ParseNode $node, int $maxDepth, int $nodeWidth, int $nodeHeight): string
{
    $display = "";
    $s = get_node_contents($parameter, $node);
    if ($node->getDepth() == 0){
        $addX = 15;
    } else {
        if ($node->getDepth() == $maxDepth){
            $addX = -5;
        } else {
            $addX = 5;
        }
    }
    $x = $node->getDepth() * $nodeHeight + $addX;
    $y = ($node->getInOrderTraversalIndex() + 1) * $nodeWidth - 20 / 2;
    if ($parameter->treebankdrawable == null){
        $display .= display_english_node_contents($parameter, $node, $x, $y, $s);
    } else {
        $display .= display_turkish_node_contents($parameter, $node, $x, $y, $s);
    }
    for ($i = 0; $i < $node->numberOfChildren(); $i++){
        $child = $node->getChild($i);
        $display .= "<line x1=\"" . ($node->getDepth() * $nodeHeight + 40) . "\" y1=\"" . (($node->getInOrderTraversalIndex() + 1) * $nodeWidth - 15) . "\" x2=\"" . ($child->getDepth() * $nodeHeight) . "\" y2=\"" . (($child->getInOrderTraversalIndex() + 1) * $nodeWidth - 15) . "\"/>\n";
        $display .= create_horizontal_parse_node($parameter, $child, $maxDepth, $nodeWidth, $nodeHeight);
    }
    return $display;
}

function create_horizontal_parse_tree(DisplayParameter $parameter): string{
    $nodeWidth = 70;
    $nodeHeight = 120;
    if ($parameter->treebankdrawable == null){
        $trees = search_english_treebank_for_word($parameter);
    } else {
        $trees = search_turkish_treebank_for_word($parameter);
    }
    $display = "";
    foreach ($trees as $parseTree) {
        $display .= "<h3>" . substr($parseTree->getName(), strrpos($parseTree->getName(), "/") + 1) . "</h3>\n";
        $display .= "<svg width=\"" . (($parseTree->maxDepth() + 1) * $nodeHeight) . "\" height=\"" . (($parseTree->getMaxInOrderTraversalIndex() + 2) * $nodeWidth) . "\">";
        $display .= create_horizontal_parse_node($parameter, $parseTree->getRoot(), $parseTree->maxDepth(), $nodeWidth, $nodeHeight);
        $display .= "</svg>\n";
    }
    return $display;
}
