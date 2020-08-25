<?php
/*
 * Copyright (C) 2020 Thamtech, LLC
 *
 * This software is copyrighted. No part of this work may be
 * reproduced in whole or in part in any manner without the
 * permission of the Copyright owner, unless specifically authorized
 * by a license obtained from the Copyright owner.
**/

namespace thamtech\di;

/**
 * A Provider provides instances. Concrete implementations will typically
 * provide instances of a certain type and are used in dependency injection.
 *
 * @author Tyler Ham <tyler@thamtech.com>
 */
interface Provider
{
    /**
     * Provide a value.
     *
     * @return mixed
     */
    public function get();
}
