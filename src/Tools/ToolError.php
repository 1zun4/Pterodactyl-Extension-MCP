<?php

declare(strict_types=1);

namespace PterodactylMcp\Tools;

use RuntimeException;

/** A failure the model should see and can react to, such as a missing permission. */
final class ToolError extends RuntimeException {}
