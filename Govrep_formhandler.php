<?php
// DEPRECATED — nothing in the site calls this file anymore
// (GovernmentSign_up.html posts to register_user.php instead).
// It duplicated register_user.php's job with weaker validation
// (no .gov.ke domain check, no institution linking, no approval
// workflow). Kept only so a stray old link doesn't 500 — safe to
// delete this file entirely once you confirm nothing external
// references it.
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'This endpoint is deprecated. Use register_user.php.']);
