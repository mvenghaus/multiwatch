<?php namespace JasonLewis\ResourceWatcher;

use JasonLewis\ResourceWatcher\Resource\ResourceInterface;

class Tracker
{
    /**
     * Array of tracked resources.
     *
     * @var array
     */
    protected $tracked = array();

    /**
     * Register a resource with the tracker.
     *
     * @param  \JasonLewis\ResourceWatcher\Resource\ResourceInterface  $resource
     * @param  \JasonLewis\ResourceWatcher\Listener  $listener
     * @return void
     */
    public function register(ResourceInterface $resource, Listener $listener)
    {
        $this->tracked[$resource->getKey()] = [$resource, $listener];
    }

    /**
     * Determine if a resource is tracked.
     *
     * @param  \JasonLewis\ResourceWatcher\Resource\ResourceInterface  $resource
     */
    public function isTracked(ResourceInterface $resource)
    {
        return isset($this->tracked[$resource->getKey()]);
    }

    /**
     * Get the tracked resources.
     *
     * @return array
     */
    public function getTracked()
    {
        return $this->tracked;
    }

    /**
     * Detect any changes on the tracked resources.
     *
     * @return void
     */
    public function checkTrackings()
    {
    	$eventCalls = [];
        foreach ($this->tracked as $name => $tracked) {
            list($resource, $listener) = $tracked;

            if (! $events = $resource->detectChanges()) {
                continue;
            }

            foreach ($events as $event) {
                if ($event instanceof Event) {
                	if (!isset($eventCalls[$name]))
	                {
		                $eventCalls[$name] = [
			                'event' => $event,
			                'listener' => $listener,
			                'files' => []
		                ];
	                }

	                $file = basename($event->getResource()->getPath());
                	$fileData = explode('.', $file);
                	$fileExtension = end($fileData);

	                if (!isset($eventCalls[$name]['files'][$fileExtension]))
	                {
		                $eventCalls[$name]['files'][$fileExtension] = [];
	                }

	                $eventCalls[$name]['files'][$fileExtension][] = $event->getResource()->getPath();
                }
            }
        }

        foreach ($eventCalls as $eventCall)
        {
	        $this->callListenerBindings($eventCall['listener'], $eventCall['event'], $eventCall['files']);
        }

    }

	/**
	 * Call the bindings on the listener for a given event.
	 *
	 * @param  \JasonLewis\ResourceWatcher\Listener $listener
	 * @param  \JasonLewis\ResourceWatcher\Event $event
	 * @param string[] $files
	 * @return void
	 */
    protected function callListenerBindings(Listener $listener, Event $event, $files = [])
    {
        $binding = $listener->determineEventBinding($event);

        if ($listener->hasBinding($binding)) {
            foreach ($listener->getBindings($binding) as $callback) {
                $resource = $event->getResource();

                call_user_func($callback, $resource, $files);
            }
        }

        // If a listener has a binding for anything we'll also spin through
        // them and call each of them.
        if ($listener->hasBinding('*')) {
            foreach ($listener->getBindings('*') as $callback) {
                $resource = $event->getResource();

                call_user_func($callback, $event, $resource, $files);
            }
        }
    }
}
