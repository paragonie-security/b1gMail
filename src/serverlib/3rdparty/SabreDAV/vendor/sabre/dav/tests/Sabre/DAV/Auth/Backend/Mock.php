<?php

namespace Sabre\DAV\Auth\Backend;

use
    Sabre\DAV,
    Sabre\HTTP\RequestInterface,
    Sabre\HTTP\ResponseInterface;

class Mock implements BackendInterface {

    public $fail = false;

    public $invalidCheckResponse = false;

    public $principal = 'principals/admin';

    function setPrincipal($principal): void {

        $this->principal = $principal;

    }

    /**
     *
     * When this method is called, the backend must check if authentication was
     * successful.
     *
     * The returned value must be one of the following
     *
     * [true, "principals/username"]
     * [false, "reason for failure"]
     *
     * If authentication was successful, it's expected that the authentication
     * backend returns a so-called principal url.
     *
     * Examples of a principal url:
     *
     * principals/admin
     * principals/user1
     * principals/users/joe
     * principals/uid/123457
     *
     * If you don't use WebDAV ACL (RFC3744) we recommend that you simply
     * return a string such as:
     *
     * principals/users/[username]
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     *
     * @return (bool|mixed|string)[]|string
     *
     * @psalm-return 'incorrect!'|list{bool, 'fail!'|mixed}
     */
    function check(RequestInterface $request, ResponseInterface $response): array|string {

        if ($this->invalidCheckResponse) {
            return 'incorrect!';
        }
        if ($this->fail) {
            return [false, "fail!"];
        }
        return [true, $this->principal];

    }

    /**
     * This method is called when a user could not be authenticated, and
     * authentication was required for the current request.
     *
     * This gives you the oppurtunity to set authentication headers. The 401
     * status code will already be set.
     *
     * In this case of Basic Auth, this would for example mean that the
     * following header needs to be set:
     *
     * $response->addHeader('WWW-Authenticate', 'Basic realm=SabreDAV');
     *
     * Keep in mind that in the case of multiple authentication backends, other
     * WWW-Authenticate headers may already have been set, and you'll want to
     * append your own WWW-Authenticate header instead of overwriting the
     * existing one.
     *
     * @return void
     */
    function challenge(RequestInterface $request, ResponseInterface $response) {

    }

}
