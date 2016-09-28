The listgame manual
===================

If you're on ##crawl on Freenode IRC, you can use listgame to see
game statistics for Crawl games played on the public Crawl servers.

The bot Sequell responds to listgame queries on ##crawl and can also
be /msged privately.

This document describes listgame: specifically, it covers the !lg and
!lm commands, as well as the specialised listgame commands (!won,
!streak, etc.)

NOTE: This document uses "listgame" to mean "!lg, !lm, etc..".
Where a specific command is described, that command is named
explicitly as "!lg", "!lm", etc.

!lg
===
Mnemonic: lg = *Last Game* or *List Game*

The !lg command queries completed games (i.e. games in the logfile) on
the public servers. !lg does not know about games that are still in
progress.

### Syntax

    !lg [<target-player> [<keyword1> <keyword2> ...]
                         [<game type>; viz 'crawl' or 'sprint']
                         [<field1><op1><value1> <field2><op2><value2> ...]
                         [s=<field1>,<field2> ;
                             cannot be used with max= or min=]
                         [x=<field1>[,<field2>,...]]
                         [o=<aggregate-function or % or N>]
                         [max=<field> or min=<field>; not usable with s=]]
                         [<keyed_option>]
                         [<option>]
                         [<number>]

Where:
- `<target-player>` may be one of:
  1. `<name>`: a player name as used on public servers (such as 'elliptic')
  2. `*` to consider games by all players
  3. `.` to consider games by the person issuing the !lg command.
  4. Omitted altogether to consider games by the player issuing the
     !lg command. Use `.` unless your !lg command line is empty. i.e.
     "!lg" is fine, but anything more complicated should use a nick,
     viz. `!lg . -tv`, `!lg . HEFE`, etc.

  The player nick may be negated by prefixing it with !. For instance:
     `!lg elliptic`  (find elliptic's last game)
     `!lg !elliptic` (find the last game by anybody other than elliptic)

  The nick may optionally be prefixed by @. For instance:
     `!lg @78291` (78291's last game)

  The nick may be prefixed with : to ignore nick aliases:
     `!lg :hugeterm` => `!lg * name=hugeterm`

- `<keyword>` is a word that the listgame parser recognises as a filter
  condition of some kind.

  See the section "[Listgame Keywords](#listgame-keywords)" for a list of keywords.

- `<game type>` is one of `sprint`, `crawl`, or `zotdef` to select games
  of that game type. If the game type is not specified, standard Crawl
  games are assumed.

- `<field><op><value>`

  An expression of the form `<field><op><value>` compares the value of a
  logfile field to the provided value using the given operator.

  See the section "[Logfile Fields](#logfile-fields)" for a list of valid logfile fields
  and "[Operators](#operators)" for a list of valid operators.

  More complex expressions may be expressed by enclosing them in `${ }`.
  For instance: `${ac + ev}>50`. See "[Expressions](#expressions)" for details.

  Values may not contain embedded spaces. Underscores in values are
  converted to spaces before running listgame queries. You may use use
  underscores or quotes for values containing spaces. These forms are
  identical:

      !lg * killer='steam dragon'
      !lg * killer="steam dragon"
      !lg * killer=steam_dragon

  A simple function call may be used on a field, such as `day(end)`.
  See "[Non-Aggregate Functions](#non-aggregate-functions)" for a list of functions.

  If you have a value that should be tested against multiple fields,
  you can use an expression of the form `x|y<op>val`. For instance:

      killer|ikiller=uniq => ((killer=uniq || ikiller=uniq))
      map|kmap!=          => ((map!= || kmap!= ))

  If both tests must match, use the `x&y<op><val>` form:

      map&kmap!=          => map!= kmap!=

- `s=<field1>[%],<field2>...` or `s=-<field>[%]`

  Mnemonic: s = Summarise by field

  For a set of games matching the listgame query, `s=<field>` counts the
  number of occurrences of each value for the field in the matching
  games.

  For instance: `!lg * s=god` finds all the gods played on all public
  servers and displays the number of games for each god.

  When summarising, results are sorted in descending order of the
  count for each group by default. To sort by ascending order instead,
  use `s=-field` instead of `s=field`

  You may use only one s=X form per query.

  If the field name is suffixed with a %, the query will also show what
  percentage of the total each group is. For instance:

      !lg * win s=name%

  Shows the names of all players who have won games and what
  percentage of the total wins on the server they have.

- `x=<field>[,<field1>,...]`

  Mnemonic: x = eXamine field

  x allows you to request extra fields for display for a given
  listgame game. The values of some fields are not displayed normally
  (for instance fields like 'str', 'int', 'dex'). Using x=field forces
  display of the value of the field.

  In addition to simple field names, you can request aggregate
  expressions for fields as x=aggregate(field).

  ###### Examples:
    `!lg * x=avg(turn)` shows average turn count of all games

    `!lg * x=avg(dur),max(turn)` shows average duration and the max turn count.

  You cannot mix `x=field` and `x=aggregate(field)` in the same listgame
  query, i.e. you can use either simple fields or aggregates, but not
  both at once.

  You can combine `x=aggregate(field)` with `s=field`:

    `!lg * s=god x=avg(xl)` summarise by god and show the average XL for players of each god.

  See the "[Aggregate Functions](#aggregate-functions)" section for a
  list of aggregate functions.

- `o=<aggregate-expr>` or `o=-<aggregate-expr>` or `o=.`
  Mnemonic: o = Order By

  When used in queries of the form:

      !lg * s=<thing> x=aggregate(field) o=aggregate(field)

  the results are sorted in descending order by the value of
  aggregate(field) instead of the default sort of the descending order
  of counts for each group.

  If you want an ascending order sort, use `o=-aggregate(field)`

  You may also want to sort by the summary group (for example, sort by
  name in an s=name query). In such cases, use `o=.` or `o=-.`

    `!lg * s=name o=-.`  (Games played by each player, ordered by player name)

  You may use only one o=X form per query.

  As a shortcut for x=aggregate(field) o=aggregate(field), you may specify
  the ordering on the aggregate expression itself:

      x=+avg(turn) => x=avg(turn) o=avg(turn)
      x=-avg(turn) => x=avg(turn) o=-avg(turn)

- `max=<field>` or `min=<field>`

  By default listgame finds the most recently completed game matching
  the search criteria. This can be changed by using max=<field> or
  min=<field> to find the game with the maximum or minimum values for
  the given field instead.

  For instance:
    `!lg * win min=dur` finds the fastest win by real time spent.

    `!lg * win max=turn` finds the slowest win by turns used.

    `!lg * win min=start` finds the winning game with the earliest start
                          time, and so on.

  You may use only one max= or min= operation per query, and you may
  not combine it with o=, or s=

- `<keyed_option>`

  Some options modify the display format for listgame. Keyed options
  are expressed as `key:"val"`. Note that values must be present and are
  always single- or double- quoted.

  ###### Recognised keyed options:
  * `fmt/format`:
     For grouped (`s=foo`) queries, the display format of the
     innermost group.

     The default fmt for inner groups is `"${n_x}${.} ${%} [${n_ratio};${x}]"`, where
     `${n_x}` produces the `"45x "` prefix in groups such as `45x HEFE`,
     `${.}` displays the actual group value, `${%}` displays the percentage
     of the total that the group represents, `${n_ratio}` displays the
     ratio of the numerator to denominator counts for ratio queries, and
     `${x}` displays any extra field expressions. You may refer to
     individual extra field expressions as `${x[0]}`, `${x[1]}`...

     You may also refer to the user issuing the command with `$user`
     and the user referenced in the command as `$name` or `$target`.
     `$target` will be set to the first name-like thing in the
     query (which may be `*`), while `$name` will look for the first
     non-* name referenced in the query. So in `!lg * s=name / @foo`,
     `$target` will be `*`, but `$name` will be `foo`.

     For ungrouped queries, this is the display format for the
     game itself. You may use any of the game fields (`$name`,
     `$turn`, `$sc`), `$x` to display `x=foo` values, and so on.  `$n`
     gives the total count of games matching the query.  Caveat: `$n`
     will not work in the `fmt` key for grouped queries, and
     `${n_x}` will not work for the `fmt` key ungrouped queries.

  * `pfmt`: For nested (`s=foo,bar`) queries, the display format of non-inner
    groups. Defaults to `${n_x}${.} ${%} (${child})`, with `${child}` being
    replaced by a comma-separated list of inner-group strings.
  * `stub`: Displayed when no results match the query condition.
  * `title`: Displayed as a prefix to grouped and aggregate queries. For
    instance:

           !lg * win s=name title:"Winners"
           => Winners: 392x ...

    When used with `-graph`, the `title:` is used as the graph title.

    The total count field `$n` will work in the title format for grouped queries.

  * `join`: The string used to join individual groups, defaulting to `", "`

- `<option>`
  Some options modify the behaviour of !lg:
  * `-log`  Get a URL to the game morgue instead of displaying the game details.
  * `-tv`   Play the game on FooTV instead of displaying details. See
    the "[FooTV](#footv)" section.
  * `-graph` For a grouping/summary query, produces a graph. See the
    "[Graphs](#graphs)" section.
  * `-ttyrec` Get URLs to the game's ttyrecs.
  * `-random` Return a random game matching the filter conditions.
  * `-count:N` For non-grouped, non-aggregate queries, request N games instead of 1. `-count:N` sets
    `fmt:'$name L$xl $char ($src)$(and $x " [$x]")'` unless `fmt:` is explicitly specified.

  These options are not applicable to summary queries (queries using `s=foo`),
  with the exception of `-graph`, which is *only* applicable to summary queries.

- `<number>`

  Given any valid listgame query, there is a set of N games matching
  the query, where N >= 0.

  For instance, given a query for wins:

      !lg * win

  Listgame will show *one* game matching the query as:

      [N]. X the Y ...  escaped with the Orb ...

  Where [N] is the number of games that matched the query. The game
  shown is always the *last* game that matched the query in whatever
  sort order is in use. The default sort order is max=end, so the
  game shown is the last ended game by default.

  If you do not want to see the *last* game, you may specify a game
  number to use instead. The default game shown is "-1", so you may
  request "-2" to see the second-last game, "-3" to see the third last
  game and so on.

  You may also use positive indexes: "1" to find the first game, "2" to
  get the second game and so on. For instance:

    `!lg * 1` (find the first game ended on any of the public servers)

    `!lg * win -1` (find the most recent winning game:: same as `!lg * win`)

    `!lg * win -2` (find the second-last winning game)

  When you combine a game number with max or min, you're using an index
  into games ordered by that field:

    `!lg * max=dur -2` (find the second longest game by real time spent)

    `!lg * win min=turn -3` (find the third fastest win by turn count)

Ratio Queries
-------------

!lg may also be used to calculate ratios using queries of the form:

    !lg * <expressions-A> s=<something> / <expressions-B>

In this case, *two* queries are executed:

    !lg * <expressions-A> s=<something>

  and

    !lg * <expressions-B> <expressions-A> s=something

i.e. a broad query is run first (expressions-A), and then narrowed
using extra conditions (expressions-B AND expressions-A), and a ratio
of the counts for A&B to the counts for A is calculated. This is handy
for calculating percentages for things such as:

  `!lg * s=god / win` (find the win rate for each god as a percentage
                       of games played)

  `!lg * DE s=char / win` (find the win rate for each deep elf class as
                           a percentage of games played)

  `!lg * Gh s=char / god=Xom` (find the percentage of Xom worshippers
                               as a percentage of Ghoul games grouped
                               by character)

When using the `!lg ... s=foo / ...` form, the groups are sorted by
descending order of percentage. You may change the sort by explicitly
specifying `o=%` (descending) or `o=-%` (ascending).

NOTE: In ratio queries you may use multiple sort conditions:

  `!lg * s=char / win o=N,%` Show win rates of different characters, sorting
                             by number of wins, and subsorting by win
                             percentage.

You may also perform rudimentary extended filtering when using the !lg
ratio form. `?: <expr>` requests extended filtering and must always be
the *last* thing on the !lg line.

`!lg * s=char / win o=% ?: N>0` (show all characters sorted by win-rate,
                                excluding unwon characters)

`!lg * s=name / win o=% ?: den.N>10` (show players by win rate, excluding
                                     players with <= 10 games.)

`!lg * s=name / win o=% ?: N>10` (show players by win rate, excluding
                                 players with <= 10 *winning games*.)

Use `%>0` or `%=0` to select groups where the *percentage* is nonzero or 0.

Use `N>0` or `N=0` to select only groups where the numerator is >0 or =0.
The numerator is the count from the more *specific* query.

Use `den.N` if you want to filter by denominator instead of numerator.
The denominator is the count from the more *general* query.

!lm
===
Mnemonic: lm = Last Milestone or List Milestone

The !lm command queries game milestones (i.e. entries in the
milestones file) for games on the public servers. !lm syntax is the
same as !lg, with a few nuances:

- You may use milestone fields (see the "[Milestone Fields](#milestone-fields)"
  section for a list) OR logfile fields in !lm queries. If you use logfile
  fields in your filter condition, your query searches for milestones
  belonging to a completed game matching that filter condition.

  For instance:

  `!lm * rune win` finds the most recent rune milestone from a winning game.

  `!lm * rune killer=centaur` finds the most recent rune milestone from
  a game where the character died to a centaur.

  In some cases you may want to check the value of a logfile field even
  though milestones also have a field of the same name. For instance, if
  you want to see the last orb milestone by a player who died on D:1, you'd
  use `!lm * orb lg:place=D:1`. Qualifying a field X as lg:X forces
  the search to use the value of X from the game itself, not from the
  milestone.

- Milestone Options:

  `-tv`   plays back the portion of the game just before and after the chosen
  milestone.
  `-log`  gets the URL to the character dump of the game corresponding to
  the chosen milestone. If the milestone is a crash milestone,
  gets the URL to the crash dump instead.
  `-ttyrec` gets the URL to the single ttyrec that contains the chosen
  milestone.
  `-random` returns a random milestone matching the filters.
  `-game` gets the game correponding to the chosen milestone.

  ###### Example:
    `!lm * rune !win -game` finds the non-winning game corresponding to
    the most recent milestone.

    You may combine `-game` with the usual `-log`, `-tv`, and `-ttyrec`
    options.

- `s=<milestone-type>`
  !lm queries provide a special form of the `s=<field>` summary operation.
  You may use `s=<milestone-type>` instead of the longer
  form: `type=<milestone-type> s=noun`

  Example:

  `!lm * s=rune` is the same as `!lm * type=rune s=noun`

  `!lm * s=abyss.enter` == `!lm * type=abyss.enter s=noun`

- `<milestone-type>=<milestone-noun>`

  !lm queries provide a special filter form "X=Y" to simplify the
  common query `type=X noun=Y`

  Example: `!lm * rune=barnacled` = `!lm * type=rune noun=barnacled`
           `!lm * god.worship=Lugonu` = `!lm * type=god.worship noun=Lugonu`


!won
====

The !won command shows winning games by the named player, or by all
players.

###### Examples:

`!won elliptic`  elliptic's winning games
`!won *`         winning games for all players

In addition to basic win stats, you can specify a number of wins to
skip. For instance:

`!won stabwound 5`  shows stabwound's win stats, skipping the first 5 wins.

!gamesby
========

!gamesby gives general stats for games played by a particular player
or by all players, and accepts !lg-style filters.


Listgame Keywords
=================

Listgame keywords are words (containing no spaces) that the listgame
parser recognises as a filter condition of some kind.

Keywords may be negated by prefixing them with !. For instance::
  `!lg * HuBe`   (find the last HuBe game)
  `!lg * !HuBe`  (find the last non-HuBe game)

Listgame keywords may be one of:

1. Species abbreviations such as 'Ke', 'Hu', etc. For any species
   abbreviation XX used as a keyword, the !lg query is restricted as
   race=XX.

   Abbreviations such as 'Hu' that may reasonably be interpreted as
   both species and class abbreviations will be rejected. You may
   disambiguate ambiguous species abbreviations using a -- placeholder
   for the class (eg: "Hu--" => Human)

2. Class abbreviations such as 'He', 'Cr', etc. For any class
   abbreviation XX used as a keyword, the !lg query is restricted as
   cls=XX.

   Abbreviations such as 'Hu' that may reasonably be interpreted as
   both species and class abbreviations will be rejected. You may
   disambiguate such class abbreviations using a -- placeholder for
   the species (eg: "--FE" => Fire Elementalist)

3. Species + Class abbreviation such as `HuWr` or `DrFE`: selects only
   games for that character combination (for instance: `HuWr` => `char=HuWr`)

4. `playable`:

   selects only character combos that are playable on the current
   development version of Crawl.

   `playable:sp` and `playable:job` may also be used to select only
   playable species and jobs respectively.

5. Death types:
   - `won` / `win` / `winning`: select only winning games.
   - `quit`                   : select only quitters
   - `left` / `leaving`       : select escapes without the orb.
   - `drown`                  : select drowning deaths.
   - `pois`                   : deaths by poison
   - `cloud`                  : deaths by cloud
   - `starvation`             : deaths by starvation

6. Place names:
   Recognisable place names such as `D:10`, `Temple`, etc. will select
   games that ended at that place. If you're looking for a place that
   the listgame parser does not recognise (such as a new portal vault),
   use the full `place=XYZ` form instead.

   Note that you can use a full place name such as "Swamp:3" to
   select games on that level, or a branch name such as "Snake" to
   select games that ended in that branch.

7. God names:

   Recognisable god name prefixes/abbreviations such as `ely`, `nem`,
   `tso`, `kik`, `jiy`.

8. Game versions:
   - `X.Y` where X and Y are numbers finds games of versions X.Y, including
     minor versions (X.Y.0, X.Y.1, etc.)
   - `X.Y.Z` finds games matching that version number exactly.

9. Public server abbreviation:
   `cao` / `cdo` / `cszo` (et al.): selects games from the corresponding
                                    public server; use `!lg * s=src` to see
                                    the public servers Sequell is aware of.

10. Player nick or nick alias:
   `@nick`: selects games by that player or nick alias. See the section
             on nick aliases below.

   `:name`: selects games by "name", ignoring nick aliases.
            `:xyz` is equivalent to `name=xyz`

11. `alpha`: selects games for alpha (unstable/development) versions.

12. `tiles`: selects games played in webtiles.

13. Tournament selector:

    `t`    : Select games played in the most recent Crawl tournament
    `t<N>` : Select games played in the tournament for year 200<N>.
             For instance `t9` selects the 2009 tourney, `t8` selects the 2008
             tourney
    `t<V>` : Select games played in the tournament for version V. For instance
             `t0.10` selects the 0.10 tourney (2012a).
    `t*`   : Select games played in any tournament.

14. Milestone Types:
    In !lm queries ONLY (i.e. not in !lg) you may use the
    milestone type as a keyword:

    `abyss.enter` `abyss.exit` `rune` `orb` `ghost`, etc.

    Example: `!lm * abyss.enter` (the last milestone for a player
                                  entering the Abyss)

15. Any text field:
    Any text field may be used as a keyword, and will be translated as
    field!=

    Examples:

        !lg * map
        => !lg * map!=

        !lg * !killer
        => !lg * killer=

        !lm * !ktyp
        => !lm * ktyp=

16. A combination of keywords separated by | to indicate alternatives.

    ###### Examples:

    `!lg * xom|nemelex` (games worshipping Xom or Nemelex)

    `!lg * alpha|0.11`  (alpha versions or any cv=0.11)

17. A negated parenthesized group of keywords separated by | to indicate
    none-of-the-alternatives.

    ###### Examples:

    `!lg * !(xom|nemelex)` (games worshipping neither Xom nor Nemelex)
    => `!lg * !xom !nemelex`

    Parentheses in keywords cannot be nested to build more complex
    conditions.


Logfile Fields
==============

This is a description of the fields available for !lg queries. Most of
the fields here are described as in the Crawl xlogfile; some of the
fields are generated by Sequell as they are loaded into the SQL
database.

`id`:
A unique numeric id (integer) associated with the game.

`v`:
Game version. Example: "0.6.0", "0.7.0-a0"

`vlong`:
Full game version. Examples: "0.15.1", "0.15.0-31-geb66e34",
"0.16-a0-296-gbdb9f68". vlong is empty for games saved by Crawl
versions that did not have this information.

`vsav`:
The major.minor version of the most recently loaded Crawl save chunk. `vsav`
may be empty if Crawl has not yet loaded any saves (this is usually the case
only on the first dungeon level).

`vsavrv`:
The Crawl version of the most recently loaded player information. `vsavrv` will
be empty unless the logfile entry is from a game that has been loaded from a
save.

`lv`:
Logfile format version

`explbr`:
Experimental branch name. Examples: "chunkless", "gods", etc. This
field may be non-empty even for stable releases of Crawl if the server
admin built Crawl on a local git branch that was not named master or
did not start with "stone_soup"

`src`:
Public server abbreviation. One of "cao", "cdo", "cszo", etc.

`sc/score`:
Game score (integer)

`name`:
Player name

`game_key/game_id/gid`:

The id (a string key) that is shared by the milestone and its
corresponding logfile entry, based on the player name, the server they
were playing on, and the start time of their game.

All milestones for the same game will share the same game_key.

game_keys should be unique to their games, barring bugs.


`race/sp/species/r`:
Character race (for instance: "Deep Dwarf")

`crace`:
Canonicalised character race with draconians merged. For instance,
when race = "Red Draconian", crace = "Draconian".

`class/cls/role/c`:
Character class or job. Example: "Berserker", "Wanderer"

`char/ch`:
Character species+class abbreviation. Example: "SENe", "DrSt"

`xl`:
Character experience level (integer)

`sk`:
Character's best skill (string)

`sklev`:
Character's best skill's level (integer)

`title`:
Character's skill title. Example: "God of Death", "Ruinous", etc.

`ktyp/ktype`:
Killer Type, or how the game ended. Example: "winning", "mon", "lava", etc.

`killer`:
The monster or thing responsible for the player's death. Example: "a
hobgoblin", "Sigmund", "a five-headed hydra", "Ghib".

`ckiller`:
Canonicalised killer: a cleaned-up killer field, with hydras merged
into one, and an attempt at recognising Pan lords. If the killer field
is empty, ckiller is set to the value of ktyp instead.
Example: "a hobgoblin", "Sigmund", "a hydra", "a pandemonium lord" etc.

`ikiller`:
Indirect killer: If the killer is a summoned monster or a band member,
this field names the monster that summoned it, or its band leader.
Example: ikiller=Mara ckiller=a player illusion killer=Johan's illusion

`kpath`:
In the case where killer!=ikiller, kpath specifies the full blame chain
accounting for the presence of the killer.
Example: "woven by Mara"

`kmod`:
In cases where the killer is a zombie or some other derived undead, or
a shapeshifter, kmod specifies the kind of undead.
Example: "zombie", "simulacrum", "shapeshifter"

`kaux`:
The thing used by 'killer' to cause the player's demise. Usually a
description of the weapon or the ranged attack used to kill the
player, or the trap that was triggered that killed the player.
Example: "a +0,+0 club", "orb of energy", "dart",
         "Shot with an arrow by a centaur"

`ckaux`:
Canonicalised kaux, a cleaned up kaux value.
Example: "club", "orb of energy", "dart", "an arrow"

`place`:
Place of death.
Example: "D:1", "Abyss", "Geh:7", "Temple"

`br`:
Branch of the dungeon at the time of death.
Example: "D", "Geh", "Temple"
NOTE: br will always be the main dungeon branch the player was in most
recently. If the player was banished from the Temple and died in the
Abyss, then place=Abyss but br=Temple. Similarly if the player visited
a Sewer on D:4 and died there, place=Sewer but br=D

`lvl`:
The depth in the player's most recent dungeon branch (integer).
Example: place=D:6 => br=D and lvl=6

`absdepth`:
The depth in the dungeon the character was at (integer). Note that
absdepth may be zero if unknown (before absdepth was recorded).

`gold`:
The amount of gold held by the character (integer).

`goldfound`:
The total gold found in the dungeon (integer).

`goldspent`:
Gold spent (integer).

`zigdeepest`:
The deepest the character has gone in a ziggurat (integer).

`zigscompleted`:
The number of ziggurats the character has completed (integer).

`scrollsused`:
The number of scrolls used (integer).

`potionsused`:
The number of potions used (integer).

`kills`:
The number of monsters slain.

`ac`, `ev`, `sh`:
The character's AC, EV, SH.

`ltyp`:
The level area type the player was in.
Example: "D", "Abyss", "Pan", "Lab", "Port"
place=Abyss => ltyp=Abyss
place=Temple => ltyp=D
place=Sewer => ltyp=Port

`hp`:
Player's hp at end of game (integer)

`mhp`:
Player's max hp at end of game (integer)

`mmhp`:
Player's unrotted max hp at end of game (integer)

`mp`:
Player's magic points at end of game (integer)

`mmp`:
Player's max magic points (integer)

`bmmp`:
Player's base max magic points (integer)

`dam`:
Damage of the killing blow (integer)

`sdam`:
Total damage done by the source of death on the player's last turn.

`tdam`:
Total damage the player sustained in their last turn.

`str`, `int`, `dex`:
Player's strength, intelligence, dexterity at end of game (integer)

`god`:
Player's god at end of game
Example: "Xom", "Nemelex Xobeh"

`piety`:
Player's piety (integer)

`pen`:
Player's penance (integer)

`wiz`:
Wizmode flag (boolean). If true, the player was in wizard mode.

`start`:
Game start time (date)

`end`:
Game end time (date)

`rstart`:
Raw game start time string. Example: "20100727203959S". Note
that the raw time string has 0-based months (i.e. January is 0 and
December is 11)

`rend`:
Raw game end time string. Same as rstart, but for the game end time.

`dur`:
Game duration in seconds (integer). Note that idle times are clamped.

`turn/turns`:
Game turn count (integer)

`aut`:
Game time in arbitrary units of time.

`maxskills/maxsk`:
All level-27 skills.

`fifteenskills/fifsk`:
All level-15 or better skills.

`status/stat`:
Character statuses such as berserk, confused, etc., as a
comma-separated string.

`urune`:
Number of unique runes in player inventory at end of game (integer).

`nrune`:
Total number of runes in player inventory at end of game (integer).

`tmsg`:
Terse game end message.

`vmsg`:
Verbose game end message. Usually identical to tmsg.

`alpha`:
true if the game is an alpha/development version, false otherwise.

`tiles`:
true if the game is in webtiles, false otherwise.

`killermap/kmap`:
The name of the map (vault) in which the monster that killed the player was
placed, if the monster was placed in a vault.

`map`:
The name of the map/vault the player was in at end of game. Example:
"hall of Zot". NOTE: underscores in map names will be replaced by spaces
when stored in the database.

`mapdesc`:
A human-readable description for the map in which the player was at
end of game, usually empty. Example: 'Sprint II: "The Violet Keep of Menkaure"'

`ntv`:
Integer count of number of times the game has been requested for FooTV.

Milestone Fields
================

Milestones have many of the same fields as logfiles, but the values for
these fields are obviously the value of that attribute at the time the
milestone was generated, not at end of game.

Fields in common with logfile:
alpha, v, cv, vsav, vsavrv, name, race, crace, cls, char, xl, sk, sklev, title,
place, br, lvl, ltyp, hp, mhp, mmhp, str, int, dex, god, dur, turn, urune,
nrune, rstart, tiles, maxskills, status, gold, goldfound, goldspent, kills, ac,
ev, sh, aut, ntv.

These fields are unique to milestones:

`id`:
A unique numeric id (integer) associated with the *milestone*. Note
that the id field for milestones is quite different from the id field
for logfile records.

`game_key/game_id/gid`:

The id (a string key) that is shared by the milestone and its
corresponding logfile entry, based on the player name, the server they
were playing on, and the start time of their game.

All milestones for the same game will share the same game_key.

`time`:
The exact time the milestone was generated (date)

`rtime`:
The raw time string for the time the milestone was generated. Example:
"20100727203959S". Note that the raw time string has 0-based months
(i.e. January is 0 and December is 11)

`verb/type`:
The type of milestone. Example: "ghost" (player killed a ghost),
"uniq" (player killed a unique), "orb" (player found the orb of Zot).
You can use "!lm * s=type" to see a list of milestone types.

`noun`:
The object of interest in the milestone. For instance if type=ghost,
noun=Foo's ghost. You can use "!lm * type=X s=noun" to see a list
of nouns for that milestone.

`milestone`:
The full description of the milestone. Example: "entered a Sewer."

`oplace`:
For milestones generated for entering a branch or portal vault, describes
the original place the player entered the branch/portal. Example:
milestone=entered a Sewer. oplace=D:3

Operators
=========

Listgame expressions may use these operators:

| Operator | Description                                  |
|----------|----------------------------------------------|
| =        | equals (important: see note below)           |
| !=       | not-equal                                    |
| ==       | exact equals                                 |
| !==      | exact not-equals                             |
| <        | less-than                                    |
| >        | greater-than                                 |
| <=       | less-than-or-equal                           |
| >=       | greater-than-or-equal                        |
| =~       | glob match (see below)                       |
| !~       | glob not match                               |
| ~~       | POSIX regexp match                           |
| !~~      | POSIX regexp not match                       |
| (( ))    | grouping parentheses (note: must be doubled) |
| ||       | Boolean OR                                   |

Inside expressions:

| Operator | Description                                  |
|----------|----------------------------------------------|
| ( )       |  grouping parentheses                        |
| + - * / % | (their standard meanings)                    |


Equals (=) is not very equal
----------------------------

The = operator is *not* an exact equality operator; it uses several
heuristics to fix common broken queries. These heuristics depend on
the field used in the comparison:

### ktyp:

For !lm queries only, using the form

    ktyp=

will query milestones with no matching game. These milestones are not
necessarily only from ongoing games, so you may need additional
filters to filter out old orphan milestones.

You may also express this as !ktyp

### maxskills:

An expression of the form maxskills=X will be converted into a search for
that maxed skill as:

    maxskills=fighting  =>  maxskills~~(?:^|,)fighting\y

(where \y is a word boundary regex match)

Multiple statuses can be matched using commas:

    maxskills=fighting,ice magic
    => maxskills~~(?:^|,)fighting\y maxskills~~'(?:^|,)ice magic\y'

In addition, common skill abbreviations such as "fi" for "Fighting" will be
expanded to their full forms:

    maxskills=fi,nec
    => maxskills=Fighting maxskills=Necromancy
    => maxskills~~(?:^|,)Fighting\y maxskills~~(?:^|,)Necromancy\y

### status:

status behaves similarly to maxskills when searching for individual
terms. Common status abbreviations will also be expanded.

### god:

Queries of the form `god=<god-abbr>` are transformed to `god=<god-full-name>`,
so for instance:

    god=nemelex => god='Nemelex Xobeh'
    god=oka     => god=Okawaru


### killermap/map:

Queries of the form `map=X` are translated as `map~~^X` to handle vaults that
use subvaults. If a map X uses a subvault S, then map==X; S, but you can
use map=X to match it anyway.

If you're summarising by maps and want to summarise by the primary vault and
ignore subvaults, you can apply the function vault(). As an example, to see
kills in HangedMan's vaults:

    !lg * map=~hangedman s=vault(map)


### killer/ckiller/ikiller:

Queries of the form killer=X (Example: `killer=hobgoblin`) are
translated as (killer='X' OR killer='a X' OR killer='an X'). This
allows users to use `killer=rat` instead of the more verbose `killer=a_rat`.

Queries of the form `killer=uniq` are translated into a query to find
all killer values that do not start with "a " and "an " and do not
contain the word "ghost".

### place:

Queries of the form `place=X` (where X is a branch that has more than
one level) are converted into the form `place=~X:*`, to match all games
in that branch. Example: `!lg * place=Orc` is the same as `!lg *
place=~Orc:*`

### race/crace:

Queries of the form race=XX where XX is a species abbreviation are
converted into the form race=Species where Species is the full name
of the species. Example: `!lg * race=Hu` => `!lg * race=Human`

### class:

Queries of the form cls=XX where XX is a class abbreviation are
converted into the form cls=Class where Class is the full name.
Example: `!lg * class=He` => `!lg * class=Healer`

### [any]:

Any `X=A|B|C` query will be converted to `((X=A || X=B || X=C))` if A, B
and C contain only letters, underscores, numbers, periods, and spaces.

Similarly any X!=A|B|C will be converted to X!=A X!=B X!=C.

If you want to test (in)equality without any mangling, use == or !==

Glob Matches
============

=~ performs a glob match, or a simple substring match.

For instance `tmsg=~hobgoblin` finds terse messages containing the
substring "hobgoblin". You may also use the * and ? wildcards, but if
you use a wildcard and want a substring match, you must prefix and
suffix your search pattern with * as: `tmsg=~*hob???lin*`

Aggregate Functions
===================

The available aggregate functions [usable as `x=aggregate(field)`]:

1. avg (average)
2. median
3. max (maximum value)
4. min (minimum value)
5. sum
6. std (standard deviation)
7. variance
8. count (count distinct)
9. cdist (count distinct, identical to 'count')

You may use `avg`, `median`, `sum`, `std` and `variance` only for
numeric fields.

`max`, `min` and `count` may also be used on text (character) fields.


Non-Aggregate Functions
=======================

Non-aggregate functions may be applied to fields in query conditions,
grouping clauses (s=X) and extra-field info (x=X).

- `length(<text>)`       Length of a text field.
- `now()`                Current time and date (UTC).
- `day(<date>)`          Truncates the date to the closest prior midnight time.
- `week(<date>)`         Truncates to midnight of the 1st day of the week
- `month(<date>)`        Truncates to midnight of the 1st day of the month
- `year(<date>)`         Truncates to midnight of the 1st day of the year
- `nhour(<date>)`        Hour as a number (0-23)
- `nmin(<date>)`         Minute as a number (0-59)
- `ndayofmonth(<date>)`  Day of the month as a number (1-31)
- `nweekofyear(<date>)`  Week of the year as a number (0-53)
- `nmonth(<date>)`       Month as a number (1-12)
- `ndayofweek(<date>)`   Day of week as a number (0-6, 0 being Sunday)
- `log(<number>)`        Base 10 logarithm of <field>, truncated down
                       to steps of 0.1.
                       Note: you probably need a field>0 condition to prevent
                       zero or negative values exploding the log() function.
- `trunc(<number>,<divisor>)`  Returns floor(number / divisor) * divisor.
- `interval(<string>)` Convert a string to a Postgres [time interval](http://www.postgresql.org/docs/9.1/static/datatype-datetime.html#DATATYPE-INTERVAL-INPUT). Note that Postgres and Sequell have different notions of what a year is. Sequell treats years as simple units of 365 days, whereas `interval('1y')` ≈ `interval('365d 6h')`
- `interval_seconds(<interval>)` Convert an interval to seconds (integer).
- `seconds_interval(<number>)` Converts a number to an interval for use in date arithmetic (for instance `x=${start + seconds_interval(dur)}`)
- `int(<number>)`      Convert a number or time interval to an integer
- `vault(<map>)`       Truncates strings of the form 'x; y; z' to 'x', i.e.
                       discarding anything after the first '; '. This is handy
                       to summarize by maps without subvaults affecting
                       summary counts.
- `subvault(<map>)`      Truncates strings of the form 'x; y; z' to 'y; z', i.e.
                       discarding everything before (and including) the
                       first '; '.
- `size(<field>)`       For comma-separated values, counts the number of values.
- `regexp_replace(<string>, <regexp>, <repl>)` Postgres [regexp_replace](http://www.postgresql.org/docs/9.1/static/functions-string.html)


Expressions
===========

You may use more complex expressions wrapped in ${ }, or after a $
(optionally terminated by another $):

    !lg * $ (ac + ev) * 2 > 15
    !lg * $ (ac + ev) * 2 > 15 $ s=hp
    !lg * ${ac+ev}>15
    !lg * turn>0 s=${sc/turn}

Function call arguments are always expressions:

    !lg * turn>0 x=avg(sc/turn)

Inside expression forms, you may use + - * / % ** (exponentiation)
operators in addition to the usual operators.


User-Defined Commands
=====================

You can define shortcut commands for !lg/!lm commands that you use frequently:

Examples:

    !cmd !lgs !lg * sprint
    !cmd !lgz !lg * zotdef

These shortcut commands can be used as:
    !lgs
    !lgz
    !lgs @person
    !lgz s=char x=avg(xl)

The usual !lg rule of the first argument being treated as a nick does
not necessarily apply to custom commands.

You may define simple placeholder variables for your custom commands:

    !cmd !unwon !lg * s=char / won @$1 ?: N=0

    !unwon elliptic => !lg * s=char / won @elliptic ?: N=0
    !unwon . =>  !lg * s=char / won @. ?: N=0

Placeholders may be of the form `$1`, `$2`, etc., or `$*` to imply "all
remaining arguments". You may define default values for missing arguments
with `${N:-default}` or `${*:-default}`. For example:

    !cmd !unwon !lg * $2 s=char / won @${1:-.} $* ?: N=0

    !unwon => !lg * s=char / won @. ?: N=0
    !unwon xyz => !lg * s=char / won @xyz ?: N=0
    !unwon xyz dg => !lg * dg s=char / won @xyz ?: N=0
    !unwon xyz dg turn<30000 => !lg * dg s=char / won @xyz turn<30000 ?: N=0

The placeholder variables operate on space-delimited words, so they
will be unable to handle conditions containing spaces, (such as
'killer=storm dragon'; underscored forms such as 'killer=storm_dragon'
will work) parenthesized groups, ratio queries (/), etc.

To get a list of user-defined commands, use `!cmd -ls`. To delete
a command, use `!cmd -rm` as:

    !cmd -rm !lgz

You can query the existing definition of a command using:

    !cmd !lgz


You may also define new keywords for common listgame filters:

    !kw dying !(boring|winning)
    !kw meatsprint sprint map=meatsprint

!kw accepts -ls and -rm switches to list and delete entries.


Nick Aliases
============

The !nick command maps IRC nicks to names used on public servers:

Syntax
------

    !nick ircnick charactername1 [charactername2 ...]
    !nick -rm ircnick
    !nick -rm . charactername1

The listgame commands normally assume that the user's IRC nick is the
same as their player name on the public server. For instance, when
a person with the IRC name 'aardvark' runs the query "!lg .", Sequell
expects that aardvark plays their games using the same name "aardvark"
on the public servers. Let's assume aardvark actually plays their games
as "kravdraa" on the public servers (as a contrived example).

aardvark may then query their games using "!lg kravdraa", or they may
create a *nick mapping* from aardvark -> kravdraa as:

    !nick aardvark kravdraa

after which aardvark can run listgame queries as "!lg ." and find
games played under the name kravdraa.

It's also possible that aardvark plays their games under two names:
aardvark and kravdraa, and would like listgame queries to find games
under both those names. aardvark can do that by running:

    !nick aardvark kravdraa aardvark

You may add additional listgame conditions to nick mappings.

    !nick gerbils-cao-games (cao) gerbil

Querying a nick with listgame conditions also applies those conditions
as filters:

    !lg gerbils-cao-games
    => !lg gerbil cao

You may create a "nick" that is entirely composed of listgame conditions:

    !nick wins (win)
    !lg wins

Avoid pure-condition nick mappings: they make it more likely that
someone will ask for a player's games and instead trigger a misleading
nick mapping. In general, prefer !kw to !nick if you're not explicitly
mapping nicks.

Once a nick mapping is established, !lg and similar commands will expand
the mapping when used as the first argument (the nick position).

You can bypass nick mappings in listgame queries by using an explicit
name=foo expression or by prefixing the name with ":":

  `!lg * name=elliptic`  finds games by elliptic, without applying nick
                         mappings.
  `!lg * :elliptic`      does likewise as does
  `!lg :elliptic`

You can explicitly request a nick mapping in a non-nick expression
with a name=@foo expression:

    !lg * name=@elliptic

You can delete a nick mapping using:

    !nick -rm <ircnick>

or remove one server character name from a mapping with:

    !nick -rm nick <servercharactername>


FooTV
=====

FooTV is a channel on termcast.develz.org (telnet termcast.develz.org
to watch) that plays games requested using the -tv option.

TV options
----------

`-tv`                starts playback of the selected game on FooTV

`-tv:cancel`         cancels playback of the selected game on FooTV

`-tv:nuke`           cancels playback of *all* previous games selected for FooTV

`-tv:new`            requests a new TV channel (automatically named) instead of
                     FooTV

`-tv:channel=<name>` requests a specifically named TV channel instead of FooTV

`-tv:<N`             starts playback N times farther from the end. -tv normally
                     starts playback a little way before the end of game, or
                     a little way before the milestone (when used with !lm).

                     If you'd like to start twice as far back, you can
                     use -tv:<2 or -tv:<N in general. N can be <1
                     (-tv:<0 or -tv:<0.5 are both valid).

`-tv:>N`             ends playback at Nx the normal time after the milestone.
                     This is only relevant when used with !lm

`-tv:>$`             continues playback to the end of the ttyrec (note: not end
                     of game)

`-tv:>>`             continues playback to the (known) end of game. Does nothing
                     when using !lg. If using !lm, and the milestone has a game
                     associated with it (i.e. the game is completed), playback
                     will continue until the end of the game. If using !lm and
                     the milestone belongs to an incomplete game, FooTV will
                     play the game up to the last timestamp recorded in the
                     game's .ts file.

`-tv:T[turncount]`   Start playback before the given turncount (if not divisible
                     by 100, rounds up to the next highest turn. For milestones,
                     this is the same as >T[turncount], i.e. this is the end of
                     playback

`-tv:>T[turncount]`  End playback near the given turncount (rounded up to
                     nearest 100)

`-tv:<T[turncount]`  Start playback near the given turncount (rounded down to
                     nearest 100). `<T0` starts from the beginning of the game.

`-tv:T[+-][delta]`   Playback to the turn count of the milestone (or end
                     of game) + the delta. i.e. `T+3000` => turn count of
                     the milestone + 3000 turns.

`-tv:x[N]`           Change playback speed. For instance, `-tv:x5` plays back
                     the game at 5x normal speed. N may be in the range [0.1,50]

TV seek options may be combined:

    !lm * br.end=Zot -tv:<0:>$

(Watch the last character who reached Zot:5 from the moment they enter
Zot:5, continuing till the end of that game session).

Play from turn 100 - 5000 (approximately) of the most recent winning game:

    !lg * win -tv:<T100:>T5000

Play most recent golden-rune milestone, but continue 3000 turns past
the milestone:

    !lm * rune=golden -tv:T+3000

Play a full game:

    !lg * win max=sc -tv:<T0

Play a full game given a milestone:

    !lm * orb min=turn -tv:<T0:>>

Turn-based playback will work only for relatively recent Crawl games.
Games from very old versions lack the timestamp files that FooTV needs
to match turn counts to UTC times. Using <T0 or <T1 to play back from
the start of game will still work even without timestamp files.

There is a subtle difference between `<T1` and `<T0`: `<T0` starts playback
from the beginning of the first ttyrec, whereas `<T1` starts at the game
start time recorded by Crawl, i.e. `<T1` will skip the character
selection screens, but `<T0` will not.

If your playback is long, please use `-tv:new` so that you don't block
FooTV for the duration of the playback.


Graphs (Experimental)
=====================

You may generate graphs for grouped queries using the -graph option:

  `!lg * t win s=name -graph` (graph of number of wins per player in the tourney)
  `!lg * win s=src -graph` (graph of wins by public server)
  `!lg * !boring s=day(end) -graph` (games completed per day, excluding quitters)
  `!lg * s=src -graph:pie` (pie chart of games played on public servers)

When generating graphs, the summary/grouping field is used as the X-axis,
and the count as the Y-axis:

  `!lg * t win s=name -graph` => player name on X, number of wins on Y

Using an x=aggregate(field) expression makes the graph choose that as the Y
axis value. For instance, to see the median intelligence of different winning
characters by god:

    !lg * win s=god x=median(int) o=-. -graph

Multi-series graphs can be produced using a double-grouping or
multiple x=foo terms:

  `!lg * s=src,tiles -graph` (show breakdown of tiles/non-tiles games on servers)

Graphs of median stats in wins by god:

    !lg * win s=god x=median(int),median(str),median(dex) -graph


###### Graph types:
 - Column (default)
 - Scatter -- only if the X-axis is numeric or a date.
 - Area -- same restrictions as Scatter
 - Pie

Command-Line Expansion
======================

!lg command lines are subject to
[Sequell's normal command-line expansion](commandline.md).


Durable references to games
===========================

### Summary

Use `gid=<the game id>` in queries that you want to bookmark so
you can consistently find the same game or milestone in future.

For !lg use:

    !lg * gid=<player:server:date> 1

For !lm use:

    !lm * gid=<player:server:date> <milestone-index>

### The Long Story

If you'd like to reference a particular game on a public server so
that others can find the game logfile, or ttyrecs, or play the game on
FooTV, you must unambiguously identify the game with a query that will
return the same result in the future.

Let's say you found a Tomb death that's interesting:

    <user> !lg * Tomb
    <Sequell> 1053. redacted the Thanatomancer (L21 DDNe of Makhleb), mangled by a reaper (a +3,+1 scythe of venom) (summoned by an indirect mummy death curse) on Tomb:1 (tomb_1) on 2014-02-21 12:21:13, with 350541 points after 58979 turns and 8:26:54.

If you add a reference to this game to the LearnDB as `!lg * Tomb`,
that query will be out-of-date the moment any other player on any
public server dies in the Tomb.

You could improve this somewhat by referencing the game number (1053):

    !lg * Tomb 1053

This says that you're requesting death #1053 in the Tomb, in
chronological order (by time of death). This is still somewhat
unsatisfactory, since if two characters die at the same second in the
Tomb, their game numbers may be swapped if Sequell's database is ever
rebuilt. You can improve things a little more by specifying the
player's name:

    !lg redacted Tomb 1

Note that specifying the player's name changes the game number
(changing any filter option changes the game number).

This reduces the likelihood of finding the wrong game when multiple
characters die at the same second, but this may still be insufficient:
a single name may be owned by two different players on two different
public servers, so same-second-deaths are still possible. To be
unambiguous, a query must identify the player, the server, and the
game start time. The best filter that uses all of these is the
game-key (aka game_id aka gid). Query the game_key using x=gid

    !lg redacted Tomb 1 x=gid

Then use the gid to frame the query you want to save:

    !lg * gid=redacted:clan:20140117111454S 1

For milestones, use the milestone index with the gid, for instance:

    !lm * gid=redacted:clan:20140117111454S br.enter=Tomb 1

It's always a good idea to include the game/milestone index, even for
milestones that should be unique, because Crawl bugs sometimes produce
duplicates.


Examples
========

`!lg`                               Show your most recent game.

`!lg .`                             (ditto)

`!lg . DEFE`                        Your most recent DEFE game.

`!lg test`                          test's most recent game.

`!lg * GhEn max=sc`                 Highest scoring GhEn game.

`!lg * !win max=sc -3`              Third-highest scoring non-winning game.

`!lg * s=name`                      Number of games played by each player

`!lg * race=Gh s=name`              Number of ghoul games played by each player

`!lg @78291 s=char`                 Characters played by 78291

`!lg * race=DS god=Xom ktyp=water`  Last Xom-worshipping Demonspawn drowning

`!lg * DS Xom drown`                (ditto)

`!lg * killer=uniq s=-killer`       Kills by uniques, rarest uniques first.

`!lg * DS x=ckaux`                  Last DS game, showing ckaux value.

`!lg * !DS`                         Last non-DS game

`!lg * ((xom || nemelex))`          Last Xom or Nemelex game.

`!lg * god=Xom|Nemelex`             (ditto)

`!lg * win min=turn`                Fastest win (turncount)

`!lg * win xl>20 min=turn`          Fastest win (turncount) for characters with
                                    XL>20.

`!lm * rune s=god`                  Summary of gods worshipped at the time of
                                    finding runes.

`!lg * s=ktyp`                      Show all the different types of death

`!lg * s=ckiller`                   Show all the different monsters/types of death

`!lg * ckiller=a_player_ghost fmt:"Ghost kills: ${n}"`
                                    Display the count of characters killed by a ghost,
                                  with special formatting.

`!lg qwqw D:10 -log`                Get the character dump for qwqw's last game
                                  that ended on D:10

`!lg @78291 Zot 1 -log`             Get the character dump for 78291's first
                                  Zot death.

`!lm * orb min=turn -tv`            Watch the fastest Orb grab (turn count) on
                                  FooTV.

`!lm * rune=golden min=xl -tv`      Watch the lowest-experience character to
                                  fetch the golden rune doing their rune grab
                                  on FooTV.

`!lm * br.enter=Tomb -tv:<0:>20`    Watch the last character to reach Tomb:3,
                                  but start playback as the character descends
                                  the stairs and play 20 times more of the
                                  ttyrec than FooTV normally would.

`!lm * rune=obsidian lg:place=Coc`  Last obsidian-rune (Geh) milestone for a game
                                  that ended in Cocytus.

`!lg * !boring s=day(end) -graph`   Graph games by date completed.

## API

See [the listgame API](api.md#listgame).
