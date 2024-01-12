# How to disable a rule on a specific file or line

All errors have an identifier with the syntax: `A.B:C:D` with
- A: The rule short name (mainly made from the class name)
- B: The error identifier (like the error level or a specific name)
- C: The line the error occurs
- D: The position of the token in the line the error occurs

NB: The four parts are optional, all those format are working
- A
- A.B
- A.B:C
- A.B:C:D
- A:C
- A:C:D
- A::D

When you want to disable a rule, you can use of the following syntax:
```twig
{# twig-cs-fixer-disable A.B:C:D #} => Apply to the whole file
{# twig-cs-fixer-disable-line A.B:C:D #} => Apply to the line of the comment
{# twig-cs-fixer-disable-next-line A.B:C:D #} => Apply to the next line of the comment
```

For instance:
```twig
{# twig-cs-fixer-disable #} => Disable every rule for the whole file
{# twig-cs-fixer-disable-line #} => Disable every rule for the current line
{# twig-cs-fixer-disable-next-line #} => Disable every rule for the next line

{# twig-cs-fixer-disable A #} => Disable the rule A for the whole file
{# twig-cs-fixer-disable A:42 #} => Disable the rule A for the line 42 of the file
{# twig-cs-fixer-disable-line A.B #} => Disable the error B of the rule A for the current line
{# twig-cs-fixer-disable-next-line A::42 #} => Disable the rule A for the next line but only for the token 42
```

You can also disable multiple errors with a single comment, by separating them
with a space or a comma:
```twig
{# twig-cs-fixer-disable A B C #} => Disable A and B and C for the whole file
{# twig-cs-fixer-disable-line A.B,C.D #} => Disable A.B and C.D for the current line
```

If you need to know the errors identifier you have/want to ignore, you can run the 
linter command with the `--debug` options. See also [the command options](command.md).
