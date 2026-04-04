<?php

use olcaytaner\AnnotatedSentence\AnnotatedCorpus;
use olcaytaner\AnnotatedSentence\AnnotatedSentence;
use olcaytaner\AnnotatedSentence\AnnotatedWord;
use olcaytaner\AnnotatedSentence\ViewLayerType;
use olcaytaner\AnnotatedTree\ParseNodeDrawable;
use olcaytaner\AnnotatedTree\Processor\Condition\IsTurkishLeafNode;
use olcaytaner\AnnotatedTree\Processor\NodeDrawableCollector;
use olcaytaner\AnnotatedTree\TreeBankDrawable;
use olcaytaner\Corpus\Sentence;
use olcaytaner\Dictionary\Dictionary\Dictionary;
use olcaytaner\Dictionary\Dictionary\Pos;
use olcaytaner\Dictionary\Dictionary\TxtWord;
use olcaytaner\Framenet\Frame;
use olcaytaner\Framenet\FrameNet;
use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\FsmMorphologicalAnalyzer;
use olcaytaner\NamedEntityRecognition\NamedEntityType;
use olcaytaner\NamedEntityRecognition\NamedEntityTypeStatic;
use olcaytaner\ParseTree\NodeCollector;
use olcaytaner\ParseTree\NodeCondition\IsEnglishLeaf;
use olcaytaner\ParseTree\ParseNode;
use olcaytaner\ParseTree\TreeBank;
use olcaytaner\Propbank\FramesetList;
use olcaytaner\Propbank\PredicateList;
use olcaytaner\WordNet\SynSet;
use olcaytaner\WordNet\WordNet;

class DisplayParameter {
    public string $corpusName;
    public AnnotatedCorpus $corpus;
    public ?TreeBank $treebank;
    public ?TreeBankDrawable $treebankdrawable;
    public string $word;
    public string $search_type;
    public bool $columnWise;
    public string $color;
    public string $field_name;
    public string $layer;
}

function pos_to_string(Pos $pos): string
{
    return match ($pos) {
        Pos::ADJECTIVE => "ADJECTIVE",
        Pos::ADVERB => "ADVERB",
        Pos::NOUN => "NOUN",
        Pos::VERB => "VERB",
        Pos::CONJUNCTION => "CONJUNCTION",
        Pos::INTERJECTION => "INTERJECTION",
        Pos::PREPOSITION => "PREPOSITION",
        Pos::PRONOUN => "PRONOUN"
    };
}

function matches_word(AnnotatedWord $currentWord, string $word): bool{
    return $currentWord->getName() == $word;
}

function matches_root_word(AnnotatedWord $currentWord, string $word): bool{
    return $currentWord->getParse()->getWord()->getName() == $word;
}

function contains_tag(AnnotatedWord $currentWord, string $word): bool{
    return str_contains($currentWord->getParse(), $word);
}

function contains_word(AnnotatedWord $currentWord, string $word): bool{
    return str_contains($currentWord->getName(), $word);
}

function matches_pos(AnnotatedWord $currentWord, string $word): bool{
    return $currentWord->getUniversalDependencyPos() == $word;
}

function matches_ner(AnnotatedWord $currentWord, string $word): bool{
    return match ($currentWord->getNamedEntityType()) {
        NamedEntityType::PERSON => "PERSON" == $word,
        NamedEntityType::ORGANIZATION => "ORGANIZATION" == $word,
        NamedEntityType::LOCATION => "LOCATION" == $word,
        NamedEntityType::MONEY => "MONEY" == $word,
        NamedEntityType::TIME => "TIME" == $word,
        NamedEntityType::NONE, NULL => false,
    };
}

function matches_slot(AnnotatedWord $currentWord, string $word): bool{
    $slot = $currentWord->getSlot();
    if ($slot !== null) {
        return $slot->getTag() == $word;
    }
    return false;
}

function matches_shallow_parse(AnnotatedWord $currentWord, string $word): bool{
    return $currentWord->getShallowParse() == $word;
}

function matches_sense(AnnotatedWord $currentWord, string $word): bool{
    return $currentWord->getSemantic() == $word;
}

function matches_predicate_sense_propbank(AnnotatedWord $currentWord, string $word): bool{
    $argumentList = $currentWord->getArgumentList();
    if ($argumentList != null){
        return $argumentList->containsPredicateWithId($word);
    }
    return false;
}

function matches_predicate_sense_framenet(AnnotatedWord $currentWord, string $word): bool{
    $frameElementList = $currentWord->getFrameElementList();
    if ($frameElementList != null){
        return $frameElementList->containsPredicateWithId($word);
    }
    return false;
}

function matches_frame(AnnotatedWord $currentWord, string $word): bool{
    $frameElementList = $currentWord->getFrameElementList();
    if ($frameElementList != null){
        foreach ($frameElementList->getFrameElements() as $frameElement){
            if (str_contains($frameElement, "$" . $word . "$")){
                return true;
            }
        }
    }
    return false;
}

function matches_frame_element(AnnotatedWord $currentWord, string $word): bool{
    $frameElementList = $currentWord->getFrameElementList();
    if ($frameElementList != null){
        foreach ($frameElementList->getFrameElements() as $frameElement){
            if (str_contains($frameElement, $word . "$")){
                return true;
            }
        }
    }
    return false;
}

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

function matches(AnnotatedWord $currentWord, string $word, string $search_type): bool{
    switch ($search_type) {
        default:
        case "full":
            return matches_word($currentWord, $word);
        case "root":
            return matches_root_word($currentWord, $word);
        case "contains":
            return contains_word($currentWord, $word);
        case "tag":
            return contains_tag($currentWord, $word);
        case "pos":
            return matches_pos($currentWord, $word);
        case "ner":
            return matches_ner($currentWord, $word);
        case "slot":
            return matches_slot($currentWord, $word);
        case "shallowparse":
            return matches_shallow_parse($currentWord, $word);
        case "sense":
            return matches_sense($currentWord, $word);
        case "predicate_sense_propbank":
            return matches_predicate_sense_propbank($currentWord, $word);
        case "predicate_sense_framenet":
            return matches_predicate_sense_framenet($currentWord, $word);
        case "frame":
            return matches_frame($currentWord, $word);
        case "frame_element":
            return matches_frame_element($currentWord, $word);
    }
}

function search_corpus_for_word(DisplayParameter $parameter): array{
    $sentences = [];
    for ($i = 0; $i < $parameter->corpus->sentenceCount(); $i++) {
        $sentence = $parameter->corpus->getSentence($i);
        for ($j = 0; $j < $sentence->wordCount(); $j++) {
            $currentWord = $sentence->getWord($j);
            if ($currentWord instanceof AnnotatedWord && matches($currentWord, $parameter->word, $parameter->search_type)) {
                $sentences[] = $sentence;
                break;
            }
        }
    }
    return $sentences;
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
    if ($node->numberOfChildren() == 0 && $parameter->treebankdrawable != null){
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

function create_slot_table(DisplayParameter $parameter): string{
    $parameter->field_name = "slot";
    if ($parameter->columnWise) {
        return create_generic_column_table($parameter);
    } else {
        return create_generic_row_table($parameter);
    }
}

function create_sentiment_table(DisplayParameter $parameter): string{
    $parameter->field_name = "sentiment";
    if ($parameter->columnWise) {
        return create_generic_column_table($parameter);
    } else {
        return create_generic_row_table($parameter);
    }
}

function create_framenet_table(DisplayParameter $parameter): string{
    $parameter->field_name = "framenet";
    if ($parameter->columnWise) {
        return create_generic_column_table($parameter);
    } else {
        return create_generic_row_table($parameter);
    }
}

function create_propbank_table(DisplayParameter $parameter): string{
    $parameter->field_name = "propbank";
    if ($parameter->columnWise) {
        return create_generic_column_table($parameter);
    } else {
        return create_generic_row_table($parameter);
    }
}

function create_sense_table(DisplayParameter $parameter): string{
    $parameter->field_name = "sense";
    if ($parameter->columnWise) {
        return create_generic_column_table($parameter);
    } else {
        return create_generic_row_table($parameter);
    }
}

function create_shallow_parse_table(DisplayParameter $parameter): string{
    $parameter->field_name = "shallowparse";
    if ($parameter->columnWise) {
        return create_generic_column_table($parameter);
    } else {
        return create_generic_row_table($parameter);
    }
}

function create_ner_table(DisplayParameter $parameter): string{
    $parameter->field_name = "ner";
    if ($parameter->columnWise) {
        return create_generic_column_table($parameter);
    } else {
        return create_generic_row_table($parameter);
    }
}

function create_pos_table(DisplayParameter $parameter): string{
    $parameter->field_name = "pos";
    if ($parameter->columnWise) {
        return create_generic_column_table($parameter);
    } else {
        return create_generic_row_table($parameter);
    }
}

function create_morphology_table(DisplayParameter $parameter): string{
    $parameter->field_name = "morphology";
    if ($parameter->columnWise) {
        return create_generic_column_table($parameter);
    } else {
        return create_generic_row_table($parameter);
    }
}

function display_column(AnnotatedWord $currentWord, string $field_name): ?string{
    switch ($field_name) {
        case "morphology":
            return $currentWord->getParse();
        case "pos":
            return $currentWord->getUniversalDependencyPos();
        case "ner":
            return NamedEntityTypeStatic::getNamedEntity($currentWord->getNamedEntityType());
        case "slot":
            if ($currentWord->getSlot() !== null) {
                return $currentWord->getSlot()->__toString();
            } else {
                return "";
            }
        case "shallowparse":
            return $currentWord->getShallowParse();
        case "sense":
            return $currentWord->getSemantic();
        case "sentiment":
            return $currentWord->getPolarityString();
        case "propbank":
            $argumentList = $currentWord->getArgumentList();
            if ($argumentList == null){
                return "";
            } else {
                $arguments = $argumentList->getArguments();
                $display = "";
                foreach ($arguments as $argument) {
                    if (str_contains($argument, "$")){
                        $items = explode( "$", $argument);
                        $display .= $items[0] . "<br>". $items[1] . "<br>";
                    } else {
                        $display .= $argument . "<br>";
                    }
                }
                return $display;
            }
        case "framenet":
            $frameElementList = $currentWord->getFrameElementList();
            if ($frameElementList == null){
                return "";
            } else {
                $frameElements = $frameElementList->getFrameElements();
                $display = "";
                foreach ($frameElements as $frameElement) {
                    if (str_contains($frameElement, "$")){
                        $items = explode( "$", $frameElement);
                        $display .= $items[0] . "<br>". $items[1] . "<br>" . $items[2] . "<br>";
                    } else {
                        $display .= $frameElement . "<br>";
                    }
                }
                return $display;
            }
        default:
            return "";
    }
}

function create_generic_column_table(DisplayParameter $parameter): string{
    $sentences = search_corpus_for_word($parameter);
    if (count($sentences) === 0) {
        return "";
    }
    $display = "<h1>" . $parameter->corpusName ."</h1>";
    foreach ($sentences as $sentence) {
        if ($sentence instanceof AnnotatedSentence){
            $display .= "<h2>" . substr($sentence->getFileName(), strrpos($sentence->getFileName(), "/") + 1) . "</h2>";
            $display .= "<table>";
            for ($j = 0; $j < $sentence->wordCount(); $j++) {
                $currentWord = $sentence->getWord($j);
                if ($currentWord instanceof AnnotatedWord) {
                    if (matches($currentWord, $parameter->word, $parameter->search_type)) {
                        $display .= "<tr><td><b><span style=\"color:" . $parameter->color . "; \">" . $currentWord->getName() . "</span></b></td><td><b><span style=\"color: " . $parameter->color . "; \">" . display_column($currentWord, $parameter->field_name) . "</span></b></td></tr>";
                    } else {
                        $display .= "<tr><td>". $currentWord->getName() . "</td><td>" . display_column($currentWord, $parameter->field_name) . "</td></tr>";
                    }
                }
            }
            $display .= "</table>";
        }
    }
    return $display;
}

function create_generic_row_table(DisplayParameter $parameter): string{
    $sentences = search_corpus_for_word($parameter);
    if (count($sentences) === 0) {
        return "";
    }
    $display = "<h1>" . $parameter->corpusName ."</h1>";
    foreach ($sentences as $sentence) {
        if ($sentence instanceof AnnotatedSentence){
            $display .= "<h2>" . substr($sentence->getFileName(), strrpos($sentence->getFileName(), "/") + 1) . "</h2>";
            $display .= "<table><tr>";
            for ($j = 0; $j < $sentence->wordCount(); $j++) {
                $currentWord = $sentence->getWord($j);
                if ($currentWord instanceof AnnotatedWord) {
                    if (matches($currentWord, $parameter->word, $parameter->search_type)) {
                        $display .= "<td><b><span style=\"color: " . $parameter->color . "; \">" . $currentWord->getName() . "</span></b></td>";
                    } else {
                        $display .= "<td>". $currentWord->getName() . "</td>";
                    }
                }
            }
            $display .= "</tr><tr>";
            for ($j = 0; $j < $sentence->wordCount(); $j++) {
                $currentWord = $sentence->getWord($j);
                if ($currentWord instanceof AnnotatedWord) {
                    if (matches($currentWord, $parameter->word, $parameter->search_type)) {
                        $display .= "<td><b><span style=\"color: " . $parameter->color . "; \">" . display_column($currentWord, $parameter->field_name) . "</span></b></td>";
                    } else {
                        $display .= "<td>" . display_column($currentWord, $parameter->field_name) . "</td>";
                    }
                }
            }
            $display .= "</tr></table>";
        }
    }
    return $display;
}

function create_table_for_word_search(string $word, WordNet $wordNet): string
{
    $display = "<table> <tr> <th>Id</th> <th>Pos</th> <th>Definition</th> <th>Synonyms</th> <th>Wiki</th> </tr>";
    $synSetList = $wordNet->getSynSetsWithLiteral($word);
    foreach ($synSetList as $synSet) {
        for ($j = 0; $j < $synSet->getSynonym()->literalSize(); $j++) {
            if ($synSet->getSynonym()->getLiteral($j)->getName() === $word) {
                $display .= "<tr><td>" . $synSet->getId() . "</td><td>" . pos_to_string($synSet->getPos()) . "</td><td>" . $synSet->getDefinition() . "</td><td>";
                if ($synSet->getWikiPage() != null){
                    $display = create_synonym($display, $j, $synSet) . "</td><td>" . "<a href=\"" . $synSet->getWikiPage() . "\"> page </a></td></tr>";
                } else {
                    $display = create_synonym($display, $j, $synSet) . "</td><td></td></tr>";
                }
                break;
            }
        }
    }
    $display .= "</table>";
    return $display;
}

function create_table_for_synonym_search(string $synonymWord, WordNet $wordNet): string
{
    $display = "<table> <tr> <th>Synonym Words</th></tr>";
    $synSetList = $wordNet->getSynSetsWithLiteral($synonymWord);
    foreach ($synSetList as $synSet) {
        if ($synSet->getSynonym()->literalSize() !== 1) {
            for ($j = 0; $j < $synSet->getSynonym()->literalSize(); $j++) {
                if ($synSet->getSynonym()->getLiteral($j)->getName() === $synonymWord) {
                    $display .= "<tr><td>";
                    $display =  create_synonym($display, $j, $synSet) . "</td></tr>";
                    break;
                }
            }
        }
    }
    $display .= "</table>";
    return $display;
}

function create_table_for_id_search(string $synsetId, WordNet $wordNet): string
{
    $display = "<table> <tr> <th>Pos</th> <th>Definition</th> <th>Synonyms</th> <th>Wiki</th> </tr>";
    $synSet = $wordNet->getSynSetWithId($synsetId);
    if ($synSet != null){
        $display .= "<tr><td>" . pos_to_string($synSet->getPos()) . "</td><td>" . $synSet->getDefinition() . "</td><td>";
        if ($synSet->getWikiPage() != null){
            $display = create_synonym($display, -1, $synSet) . "</td><td>" . "<a href=\"" . $synSet->getWikiPage() . "\"> page </a></td></tr>";
        } else {
            $display = create_synonym($display, -1, $synSet) . "</td><td></td></tr>";
        }
        $display .= "</table>";
    }
    return $display;
}

function create_prop_bank_table(FramesetList $turkishPropBank, string $synsetId): string
{
    $display = "<table> <tr> <th>Arg</th> <th>Function</th> <th>Description</th> </tr>";
    $frameSet = $turkishPropBank->getFrameSet($synsetId);
    foreach ($frameSet->getFramesetArguments() as $arg) {
        $display .= "<tr><td>" . $arg->getArgumentType() . "</td><td>" . $arg->getFunction() . "</td><td>" . $arg->getDefinition() . "</td></tr>";
    }
    $display .= "</table>";
    return $display;
}

function create_prop_bank_table_for_multiple_synsets(FramesetList $turkishPropBank, array $synsets): string
{
    $display = "<table> <tr> <th>Id</th> <th>Definition</th> <th>Arg</th> <th>Function</th> <th>Description</th> </tr>";
    foreach ($synsets as $synSet) {
        $frameSet = $turkishPropBank->getFrameSet($synSet->getId());
        if ($frameSet != null) {
            foreach ($frameSet->getFramesetArguments() as $arg) {
                $display .= "<tr><td>" . $synSet->getId() . "</td><td>" . $synSet->getDefinition() . "</td><td>" . $arg->getArgumentType() . "</td><td>" . $arg->getFunction() . "</td><td>" . $arg->getDefinition() . "</td></tr>";
            }
        }
    }
    $display .= "</table>";
    return $display;
}

function create_predicate_table(PredicateList $englishPropBank, string $predicateName): string
{
    $display = "<table> <tr> <th>Id</th> <th>Name</th> <th>Descr</th> <th>f</th> <th>n</th> </tr>";
    $predicate = $englishPropBank->getPredicate($predicateName);
    for ($i = 0; $i < $predicate->size(); $i++) {
        $roleSet = $predicate->getRoleSet($i);
        for ($j = 0; $j < $roleSet->size(); $j++) {
            $display .= "<tr><td>" . $roleSet->getId() . "</td><td>" . $roleSet->getName() . "</td>";
            $role = $roleSet->getRole($j);
            $display .= "<td>" . $role->getDescription() . "</td><td>" . $role->getF() . "</td><td>" . $role->getN() . "</td></tr>";
        }
    }
    $display .= "</table>";
    return $display;
}

function create_role_set_table(PredicateList $englishPropBank, string $roleSetName): string
{
    $display = "<table> <tr> <th>Descr</th> <th>f</th> <th>n</th> </tr>";
    foreach ($englishPropBank->getLemmaList() as $lemma) {
        $predicate = $englishPropBank->getPredicate($lemma);
        for ($i = 0; $i < $predicate->size(); $i++) {
            $roleSet = $predicate->getRoleSet($i);
            if ($roleSet->getId() === $roleSetName){
                $display = $roleSet->getName() . "<br>" . $display;
                for ($j = 0; $j < $roleSet->size(); $j++) {
                    $role = $roleSet->getRole($j);
                    $display .= "<tr><td>" . $role->getDescription() . "</td><td>" . $role->getF() . "</td><td>" . $role->getN() . "</td></tr>";
                }
            }
        }
    }
    $display .= "</table>";
    return $display;
}

function create_morphological_analysis_table(FsmMorphologicalAnalyzer $fsm, string $sentence): string
{
    $s = new Sentence($sentence);
    $analyzedSentence = $fsm->morphologicalAnalysisFromSentence($s);
    $display = "<table> <tr> <th>Word</th> <th>Morphological Analyses</th> </tr>";
    for ($i = 0; $i < $s->wordCount(); $i++) {
        $display .= "<tr><td>" . $s->getWord($i)->getName() . "</td><td>";
        $fsmParseList = $analyzedSentence[$i];
        for ($j = 0; $j < $fsmParseList->size(); $j++) {
            if ($j !== 0){
                $display .= " ";
            }
            $display .= $fsmParseList->getFsmParse($j)->getFsmParseTransitionList();
        }
        $display .= "</td></tr>";
    }
    $display .= "</table>";
    return $display;
}

function display_word_properties(Dictionary $turkishDictionary, string $word): string{
    $wordName = $word;
    $lastChar = mb_substr($word, -1);
    $lastTwo = mb_substr($word, -2);
    $exceptLastTwo = mb_substr($word, 0, strlen($word) - 2);
    $secondLast = $lastTwo[0];
    $wordObject = $turkishDictionary->getWordWithName($word);
    $display = "";
    if ($wordObject instanceof TxtWord) {
        if ($wordObject->nounSoftenDuringSuffixation()) {
            switch ($lastChar) {
                case "ç":
                    $display = $wordName . "(cı):";
                    break;
                case "k":
                    $display = $wordName . "(ğı):";
                    break;
                case "t":
                    $display = $wordName . "(dı):";
                    break;
                case "p":
                    $display = $wordName . "(bı):";
                    break;
            }
        } else {
            if ($wordObject->isPortmanteauEndingWithSI()) {
                $display = $exceptLastTwo . "(" . $lastTwo . "):";
            } else {
                if ($wordObject->isPortmanteauFacedSoftening()) {
                    switch ($secondLast) {
                        case "ğ":
                            $display = $exceptLastTwo . "k(" . $lastTwo . "):";
                            break;
                        case "c":
                            $display = $exceptLastTwo . "ç(" . $lastTwo . "):";
                            break;
                        case "b":
                            $display = $exceptLastTwo . "p(" . $lastTwo . "):";
                            break;
                        case "d":
                            $display = $exceptLastTwo . "t(" . $lastTwo . "):";
                            break;
                    }
                } else {
                    if ($wordObject->duplicatesDuringSuffixation()){
                        $display = $wordName . "(" . $lastChar . $lastChar . "ı):";
                    } else {
                        if ($wordObject->endingKChangesIntoG()){
                            $display = $wordName . "(gi):";
                        } else {
                            if ($wordObject->vowelAChangesToIDuringYSuffixation()){
                                $display = str_ends_with($wordName, "a") ? $wordName . "(ıyor):" : $wordName . "(iyor):";
                            } else {
                                $display = $word . ":";
                            }
                        }
                    }
                }
            }
        }
        $flags = [];
        if ($wordObject->isProperNoun()) {
            $flags[] = "Özel İsim";
        }
        if ($wordObject->isPlural()) {
            $flags[] = "Çoğul";
        }
        if ($wordObject->isNominal()) {
            $flags[] = "Cins İsim";
        }
        if ($wordObject->isPortmanteau()) {
            $flags[] = "Bileşik İsim";
        }
        if ($wordObject->isAbbreviation()) {
            $flags[] = "Kısaltma";
        }
        if ($wordObject->isVerb()) {
            $flags[] = "Fiil";
        }
        if ($wordObject->isAdjective() || $wordObject->isPureAdjective()) {
            $flags[] = "Sıfat";
        }
        if ($wordObject->isAdverb()) {
            $flags[] = "Zarf";
        }
        if ($wordObject->isPronoun()) {
            $flags[] = "Zamir";
        }
        if ($wordObject->isPostP()) {
            $flags[] = "Edat";
        }
        if ($wordObject->isNumeral()) {
            $flags[] = "Sayı";
        }
        if ($wordObject->isConjunction()) {
            $flags[] = "Bağlaç";
        }
        if (count($flags) > 0){
            $display .= " " . $flags[0];
            for ($i = 1; $i < count($flags); $i++) {
                $display .= ", " . $flags[$i];
            }
        }
        if ($wordObject->notObeysVowelHarmonyDuringAgglutination()) {
            $display .= "<p> Bu kelime ünlü uyumuna uymaz </p>";
        }
    } else {
        $display = "Kelime bulunamadı";
    }
    return $display;
}

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