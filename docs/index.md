---
layout: home
hero:
  name: doctrineviz
  text: Your database schema, drawn.
  tagline: Turn a Doctrine DBAL schema into a clear, portable Graphviz diagram.
  image:
    src: /logo.svg
    alt: Connected database tables
  actions:
    - theme: brand
      text: Get started
      link: /guide/installation
    - theme: alt
      text: Command reference
      link: /guide/usage
features:
  - title: The actual schema
    details: Tables, keys, types, defaults and nullability from your database, including tables outside ORM mappings.
  - title: Portable output
    details: Generate DOT in Symfony or directly from PHP. Use Graphviz to render SVG, PNG or PDF.
  - title: Tested relationships
    details: Composite foreign keys and self-references, with opt-in links for Gedmo translation tables.
---

<div style="max-width: 960px; margin: 2rem auto; padding: 0 24px">

![Synthetic author and book schema](/example.svg)

</div>
