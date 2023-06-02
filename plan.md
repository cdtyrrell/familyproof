To Do
-----
** add informant field to source[x], 
    make info fields work/store info from new source[x]
    source lookup for log [x]

add editing a source's info to dashboard [ ]
assertion: save/update quality and assessment features in evidence [ ]
make "view" pages (family group sheet, pedigree, person details/timeline, gedcom export, & pdf versions)
** if there is conclusion info, but the assertion has been reverted because of new evidence, make this visible but in a disabled style.

OVERALL FLOW
============

Start: Dashboard, choose person and question, go to research log.
At log, review previous work, then begin a new search; enter details like repo and search terms; connect or create sources
After a session or 'reasonably exhaustive' search, go to a source.
At source, transcribe information (content). Return to dashboard.
From dashboard, select an/the assertion to review.
At assertion, connect info via evidence statements.
Analyze evidences, correlate, resolve conflicts and conclude.
** Only after concluding will the information be visible/available on "view" pages or export.


Dashboard (index.php) 
    --> Add New Person = insert new subject record
    --> Go To Log = 
    --> Details
    --> Pedigree
    --> Group Sheet
    --> Export
    --> Review

Log
        / none --> insert new Assertion --> ?rlid  (aka P+T+N)
    P+T - one ---- resolve id ------------> ?rlid
        \ many --> choose ----------------> ?rlid

   / returning (has sourceid) --> insert new RL source --\ 
RL                                                         (DISPLAY) | New RL 
\  \ fresh (no sourceid) --------------->----------------/                /
 \                                                                       /
  \----------<-------- insert new Assertion -------------<--------------/

--> Add New Question = insert new questions record

Pages

Dashboard
    New search
        select person, then event type; click go => takes you to log page
    Review previous
        List of 'needs review' assertions, sorted by oldest-newest or person or heading/fact
        assertions.php

Assertion.php
    each person and event (how to deal with multiple marriage, child, etc.)
    Needs review
        Information that remains unanalyzed.
        When a new piece of information is added that is germane to a conclusion, the conclusion reverts to 'needs review'


Research Log
    one per each person + event type combination,
    date automatic, enter repository and search params, add new source => Source & Info
    if no research, create new

Source & Information
    New or existing source data
    Choose or Create new Person Record
    Enter information (heading/fact, data, context)
    On submit > any heading/fact that has not previously been documented for person initiates a new assertion record and new evidence record

    Make a grid (16 x 32), with headings/facts and rows for data


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
Person Details
    Timeline
        Facts and relationships whose information & evidence has been analyzed and asserted
    Relationships
        Derived from assertions

Family Group sheet

Pedigree