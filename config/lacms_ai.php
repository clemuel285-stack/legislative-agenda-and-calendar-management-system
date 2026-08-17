<?php
declare(strict_types=1);

/*
 * LACMS local AI configuration.
 *
 * Ollama is optional. The reminder workflow still works when Ollama is
 * unavailable because LACMS falls back to deterministic approved templates.
 */
if(!defined('LACMS_OLLAMA_ENABLED')) {
    define('LACMS_OLLAMA_ENABLED', true);
}
if(!defined('LACMS_OLLAMA_URL')) {
    define('LACMS_OLLAMA_URL', 'http://127.0.0.1:11434/api/generate');
}
if(!defined('LACMS_OLLAMA_MODEL')) {
    define('LACMS_OLLAMA_MODEL', 'llama3.2:3b');
}
if(!defined('LACMS_OLLAMA_TIMEOUT')) {
    define('LACMS_OLLAMA_TIMEOUT', 20);
}
