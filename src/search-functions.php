<?php

use olcaytaner\AnnotatedSentence\AnnotatedWord;
use olcaytaner\NamedEntityRecognition\NamedEntityType;

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

function matches_ud_tag(AnnotatedWord $currentWord, string $word): bool{
    return $currentWord->getUniversalDependency()->__toString() == $word;
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
        case "udtag":
            return matches_ud_tag($currentWord, $word);
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
