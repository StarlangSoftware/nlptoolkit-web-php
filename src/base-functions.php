<?php

use olcaytaner\Amr\Corpus\AmrCorpus;
use olcaytaner\AnnotatedSentence\AnnotatedCorpus;
use olcaytaner\AnnotatedTree\TreeBankDrawable;
use olcaytaner\ParseTree\TreeBank;

class DisplayParameter {
    public string $corpusName;
    public AnnotatedCorpus $corpus;
    public AmrCorpus $amrCorpus;
    public ?TreeBank $treebank;
    public ?TreeBankDrawable $treebankdrawable;
    public string $word;
    public string $search_type;
    public bool $columnWise;
    public string $color;
    public string $field_name;
    public string $layer;
}

function create_merged_corpus(string $folder): AnnotatedCorpus{
    $first = true;
    foreach (glob($folder . "/*") as $subDirectory) {
        if (is_dir($subDirectory) && !str_contains($subDirectory, ".git")) {
            if ($first){
                $corpus = new AnnotatedCorpus($subDirectory);
                $first = false;
            } else {
                $corpus->combine(new AnnotatedCorpus($subDirectory));
            }
        }
    }
    return $corpus;
}
