<?php

it('redirects guests from / to login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login', absolute: false));
});
