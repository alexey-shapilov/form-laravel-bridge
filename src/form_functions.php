<?php

declare(strict_types=1);

use Symfony\Component\Form\FormView;

if (!function_exists('form')) {
    function form(FormView $form) {
        return app()->make(\Symfony\Component\Form\FormRenderer::class)->renderBlock($form, 'form');
    }
}

if (!function_exists('form_start')) {
    function form_start(FormView $form) {
        return app()->make(\Symfony\Component\Form\FormRenderer::class)->renderBlock($form, 'form_start');
    }
}

if (!function_exists('form_end')) {
    function form_end(FormView $form) {
        return app()->make(\Symfony\Component\Form\FormRenderer::class)->renderBlock($form, 'form_end');
    }
}
