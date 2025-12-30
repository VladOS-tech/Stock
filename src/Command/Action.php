<?php

namespace Warehouse\Command;

enum Action: string
{
    case HOLD = 'hold';
    case CONFIRM = 'confirm';
}