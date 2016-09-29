<?php

namespace Pingdom;

/**
 * Client object for executing commands on a web service.
 */
class Client
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $token;

    /**
     * @param string $username
     * @param string $password
     * @param string $token
     * @return Client
     */
    public function __construct($username, $password, $token)
    {
        $this->username = $username;
        $this->password = $password;
        $this->token    = $token;

        return $this;
    }

    /**
     * Returns the username.
     *
     * @return string
     */
    protected function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the password.
     *
     * @return string
     */
    protected function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the token.
     *
     * @return string
     */
    protected function getToken()
    {
        return $this->token;
    }

    /**
     * Returns a list overview of all checks
     *
     * @return array
     * @throws \Exception
     */
    public function getChecks()
    {
        $client = new \Guzzle\Service\Client('https://api.pingdom.com/api/2.0');

        /** @var $request \Guzzle\Http\Message\Request */
        $request = $client->get('checks', array('App-Key' => $this->token));
        $request->setAuth($this->username, $this->password);
        $response = $request->send();
        $response = json_decode($response->getBody(), true);

        return $response['checks'];
    }

    /**
     * Returns a list of all Pingdom probe servers
     *
     * @return Probe\Server[]
     */
    public function getProbes()
    {
        $client = new \Guzzle\Service\Client('https://api.pingdom.com/api/2.0');

        /** @var $request \Guzzle\Http\Message\Request */
        $request = $client->get('probes', array('App-Key' => $this->token));
        $request->setAuth($this->username, $this->password);
        $response = $request->send();
        $response = json_decode($response->getBody(), true);
        $probes   = array();

        foreach ($response['probes'] as $attributes) {
            $probes[] = new Probe\Server($attributes);
        }

        return $probes;
    }

    /**
     * Return a list of raw test results for a specified check
     *
     * @param int        $checkId
     * @param int        $limit
     * @param array|null $probes
     * @return array
     */
    public function getResults($checkId, $limit = 100, array $probes = null, $from, $to, $offset)
    {
        $client = new \Guzzle\Service\Client('https://api.pingdom.com/api/2.0');

        /** @var $request \Guzzle\Http\Message\Request */
        $request = $client->get('results/' . $checkId, array('App-Key' => $this->token));
        $request->setAuth($this->username, $this->password);
        $request->getQuery()->set('limit', $limit);
        $request->getQuery()->set('from', $from);
        $request->getQuery()->set('to', $to);
        $request->getQuery()->set('offset', $offset);

        if (is_array($probes)) {
            $request->getQuery()->set('probes', implode(',', $probes));
        }

        $response = $request->send();
        $response = json_decode($response->getBody(), true);

        return $response['results'];
    }
    /**
     * Get a list of status changes for a specified check and time period.
     * If order is speficied to descending, the list is ordered by newest first.
     * (Default is ordered by oldest first.)
     *
     * @return array
     * @throws \Exception
     */
    public function getOutageResults($checkId, $from, $to)
    {
        $client = new \Guzzle\Service\Client('https://api.pingdom.com/api/2.0');

        /** @var $request \Guzzle\Http\Message\Request */
        $request = $client->get('summary.outage/' . $checkId, array('App-Key' => $this->token));
        $request->setAuth($this->username, $this->password);
        $request->getQuery()->set('from', $from);
        $request->getQuery()->set('to', $to);
        $response = $request->send();
        $response = json_decode($response->getBody(), true);

        return $response['summary']["states"];
    }
    /**
     * Get the average time / uptime value for a specified check and time period.
     * @return array
     * @throws \Exception
     */
    public function getTotalUptime($checkId, $from, $to)
    {
        $client = new \Guzzle\Service\Client('https://api.pingdom.com/api/2.0');

        /** @var $request \Guzzle\Http\Message\Request */
        $request = $client->get('summary.average/' . $checkId, array('App-Key' => $this->token));
        $request->setAuth($this->username, $this->password);
        $request->getQuery()->set('includeuptime', 'true');
        $request->getQuery()->set('from', $from);
        $request->getQuery()->set('to', $to);
        $response = $request->send();
        $response = json_decode($response->getBody(), true);
        return $response['summary']["status"];
    }
    /**
     * Get Intervals of Average Response Time and Uptime During a Given Interval
     *
     * @param int $checkId
     * @param string $resolution
     * @return array
     */
    public function getPerformanceSummary($checkId, $resolution = 'hour', $from, $to)
    {
        $client = new \Guzzle\Service\Client('https://api.pingdom.com/api/2.0');

        /** @var $request \Guzzle\Http\Message\Request */
        $request = $client->get('summary.performance/' . $checkId, array('App-Key' => $this->token));
        $request->setAuth($this->username, $this->password);
        $request->getQuery()->set('resolution', $resolution);
        $request->getQuery()->set('includeuptime', 'true');
        $request->getQuery()->set('from', $from);
        $request->getQuery()->set('to', $to);

        $response = $request->send();
        $response = json_decode($response->getBody(), true);

        return $response['summary'][$resolution . 's'];
    }
}
