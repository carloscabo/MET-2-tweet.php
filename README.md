# MET-2-tweet.php

One of my favorite starting pages is [MET Museum catalog](http://www.metmuseum.org/art/collection?when=A.D.+1900-present&ft=*&noqs=true&rpp=90&pg=10#!?offset=0&pageSize=0&sortBy=Relevance&sortOrder=asc&perPage=100). Almost everytime I see a delightful artwork that I want to share with my twitter followers. That the function of this scrpt.

This simple PHP script, scraps the information from a catalog page and downloads the main image, composes a tweet and sends it.

Each artwork has an unique **catalog ID** you can get fromt its URL:
```
http://www.metmuseum.org/art/collection/search/283277
// Catalog ID is 283277
```

# Usage

```bash
// Test mode, show info in term, does not post tweet
// Useful for debugging
php -f MET-2-tweet.php XXXXX

// Add --send to post tweet
php -f MET-2-tweet.php XXXXX --send
```