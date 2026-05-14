<?php

it('shows the public landing page to guests', function () {
    $response = $this->get('/');

    $response->assertOk();
});
