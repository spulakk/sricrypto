<?php

function outputError(string $error): void
{
    echo '<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">';
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
	echo $error;
	echo '</div>';
}

function outputSuccess(string $message): void
{
	echo '<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">';
	echo '<button type="button" class="aaa btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
	echo $message;
	echo '</div>';
}

function jsify($input): string
{
    if(is_array($input) || is_object($input))
    {
        return json_encode($input);
    }
    elseif(is_bool($input))
    {
        return $input ? "'true'" : "'false'";
    }
    elseif(is_string($input))
    {
        return "'$input'";
    }
    elseif(is_int($input) || is_float($input))
    {
        return (string) $input;
    }
}