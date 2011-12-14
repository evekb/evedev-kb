<?php
// EDK does not handle coffee, having only a teapot
// TODO: handle requests for tea.
//
header($_SERVER["SERVER_PROTOCOL"]." 418 I'm a teapot");
?><!DOCTYPE HTML><html><head><title>418 I'm a teapot</title></head><body><h1>I'm a teapot</h1><p>Request conflicts with system configuration.</p></body></html>