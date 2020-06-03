# Coding Standards

- [PSR-1 Basic Coding Standards](https://www.php-fig.org/psr/psr-1/)

## HTML Attribute Order
Put HTML attributes in the following order for the sake a readability and consistency:
1. Descriptors first, since they describe what the element is.
    1. `class`
    1. `type`
    1. `role`
    1. `id`
    1. `for`
    1. `placeholder`
    1. `href`
    1. `src`
    1. `value`
1. Everything else last, since they get into the nitty gritty.
    1. `data-*` (in alphabetical order)
    1. `aria-*` (in alphabetical order)
    1. `target`
    1. `action`
    1. `method`
    1. `enctype`
    1. `maxlength`
    1. `rows`
    1. `tabindex`
    1. `required`
    1. `disabled`
    1. `style`
