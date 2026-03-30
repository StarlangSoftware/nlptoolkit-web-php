<?php

use olcaytaner\AnnotatedSentence\AnnotatedCorpus;
use olcaytaner\AnnotatedSentence\AnnotatedSentence;
use olcaytaner\AnnotatedSentence\AnnotatedWord;
use olcaytaner\Corpus\Sentence;
use olcaytaner\Dictionary\Dictionary\Dictionary;
use olcaytaner\Dictionary\Dictionary\Pos;
use olcaytaner\Dictionary\Dictionary\TxtWord;
use olcaytaner\Dictionary\Dictionary\Word;
use olcaytaner\Framenet\Frame;
use olcaytaner\Framenet\FrameNet;
use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\FsmMorphologicalAnalyzer;
use olcaytaner\Propbank\FramesetList;
use olcaytaner\Propbank\PredicateList;
use olcaytaner\WordNet\SynSet;
use olcaytaner\WordNet\WordNet;

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
        Pos::PRONOUN => "PRONOUN",
        default => "",
    };
}

function matches_word(Word $currentWord, string $word): bool{
    return $currentWord->getName() == $word;
}

function search_corpus_for_word(AnnotatedCorpus $corpus, string $word): array{
    $sentences = [];
    for ($i = 0; $i < $corpus->sentenceCount(); $i++) {
        $sentence = $corpus->getSentence($i);
        for ($j = 0; $j < $sentence->wordCount(); $j++) {
            $currentWord = $sentence->getWord($j);
            if (matches_word($currentWord, $word)) {
                $sentences[] = $sentence;
                break;
            }
        }
    }
    return $sentences;
}

function create_morphology_table(string $corpusName, AnnotatedCorpus $corpus, string $word): string{
    $sentences = search_corpus_for_word($corpus, $word);
    if (count($sentences) > 0) {
        $display = "<h1>" . $corpusName ."</h1>";
        foreach ($sentences as $sentence) {
            if ($sentence instanceof AnnotatedSentence){
                $display .= "<h2>" . substr($sentence->getFileName(), strrpos($sentence->getFileName(), "/") + 1) . "</h2>";
                $display .= "<table>";
                for ($j = 0; $j < $sentence->wordCount(); $j++) {
                    $currentWord = $sentence->getWord($j);
                    if ($currentWord instanceof AnnotatedWord) {
                        if (matches_word($currentWord, $word)) {
                            $display .= "<tr><td><b><font color='red'>". $currentWord->getName() . "</font></b></td><td><b><font color='red'>" . $currentWord->getParse() . "</font></b></td></tr>";
                        } else {
                            $display .= "<tr><td>". $currentWord->getName() . "</td><td>" . $currentWord->getParse() . "</td></tr>";
                        }
                    }
                }
                $display .= "</table>";
            }
        }
        return $display;
    }
    return "";
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
        if ($frameSet != null && $frameSet != null) {
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
    if ($predicate != null) {
        for ($i = 0; $i < $predicate->size(); $i++) {
            $roleSet = $predicate->getRoleSet($i);
            for ($j = 0; $j < $roleSet->size(); $j++) {
                $display .= "<tr><td>" . $roleSet->getId() . "</td><td>" . $roleSet->getName() . "</td>";
                $role = $roleSet->getRole($j);
                $display .= "<td>" . $role->getDescription() . "</td><td>" . $role->getF() . "</td><td>" . $role->getN() . "</td></tr>";
            }
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
?>