# Mockery

A simple proxy script that allows you to rapidly mock a server.

## Why was this project created?

I've written an app that relies on GitLab's API, but I want to get rid of GitLab as a dependency. Replicating the GitLab API in PHP was going to take a long time, as there are multiple calls to the same endpoint and generating a tree view based on that information was time consuming. So I wrote Mockery

## What does it do?

It makes a copy of a URL and stores all the data that is returned as simple echo-based PHP scripts. Let's take my GitLab API app as an example.

The URL to access the GitLab server is `http://gitlab`. My GitLab API app lets me change that URL, so I point it to `http://localhost/mockery`. I then edit `index.php` and tell it where my original URL is (`http://gitlab/`), and where I'd like to save the files (`../gitlab-fake`).

Now when I open my app, Mockery will silently forward the request on to GitLab. The data will be returned to me as normal. However if I go into the other folder I specified, I'll find lots of folders and PHP files that match up to the layout of GitLab's API.

I can then change my GitLab API application to point to `http://localhost/gitlab-fake` (or whatever I called it), and the app will mostly function.

## What doesn't this do?

Most things. It's a dumb little forwarder with a recursive `mkdir` and a `file_put_contents` tacked on. It's designed for my own use, but could be useful for someone else.

## How do I install it?

Copy all the files into your `public_html` / `www` / `htdocs` folder into a subfolder called `mockery`. Edit `index.php`

If you need percentage encoded URLs, be sure to set [allowencodedslashes](http://httpd.apache.org/docs/2.2/mod/core.html#allowencodedslashes) otherwise this thing'll fold faster than Superman on laundry day.

## Bugs and things that need to be fixed

 - Most things.
 - Requests for $\_GET parameters might be a bit wonky 
