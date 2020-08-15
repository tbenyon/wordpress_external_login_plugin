<?php

function exlogCustomShouldExcludeUser($userData) {
    if (has_filter(EXLOG_HOOK_FILTER_CUSTOM_EXCLUDE)) {
        return apply_filters(
            EXLOG_HOOK_FILTER_CUSTOM_EXCLUDE,
            $userData
        );
    }
    return false;
}
