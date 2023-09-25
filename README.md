# FamilyProof
A minimalistic PHP/Mariadb database for family history research that encourages following the genealogical proof standard.

## Concepts
**Subject**  
Subjects are people--real or presumed--that appear in your genealogy. All aspects of a person are subject to the research process (including their date of birth, sex, name, and even their existance!). Thus, a Subject initially only exists as a unique number internal to the database, however, a stand-in (unsubstaniated) name is used in order to aid the researcher in separating Subjects. 

**Research Question**  
A Research Question (or, simply, "Question") is a query that the researcher is seeking to answer for a Subject. Some common Questions are pre-loaded, but additional Questions may be added by the researcher. Ideally, Questions should be general (e.g., "When and where was SUBJECT born?"). Each Subject will eventally have scores of Questions applied to them in order to develop a complete picture of who they are/were. 

**Sources**  
Sources are items like governmental documents, photos, letters, or narratives. Sources are like containers, they hold various pieces of data and information. Sources have provenance (original, derived, authored) depending on how they were generated or produced. The data on or in a Source is Information. 

**Information**  
Information has three properties: the informant, the context, and the content. For example, in a census record, the column heading and row would provide the context, the text written in the box is the content and the informant may or may not be identified (sometimes using a circle within the list of household individuals). Information varys in quality (primary, secondary, indeterminable) based on the implicit or explicit relationship between the informant and content/context. For example, a marriage date provided by the bride or groom would be of primary quality because they particapted in said event. The same date provided by an aunt may be primary (if the aunt attended) or secondary (if she generally knew of the wedding). Since we, the researcher, cannot really know, wedding date from an aunt would be of indeterminable quality. The same date provided by an adult child of the couple (born after the wedding), would be of secondary quality because the child was not present.

**Evidence**  
Evidence statments correlate an Information item with a Subject + Research Question pair.  Evidence has three modes (direct, indirect or negative) based on how strongly it links the Information content with the Subject + Research Question. 

**Analysis/Assessment**  
An Analysis/Assessment is performed to review all Evidence statements (collected to-date) as they relate to the Subject + Research Question pair. Data conflicts are weighed based on the priciples of genealogical research and recorded in the Analysis/Assessment. The Analysis/Assessment documents the researcher's thinking and evaluation of the corresponding Source provenances, Information qualities and Evidence modes. 

**Assertion/Conclusion**  
An Assertion/Conclusion is an answer to a Subject + Research Question pair. The Assertion/Conclusion emerges from the research process and cannot exist on its own. Healthy Assertions/Conclusions are dynamic and will evolve or protentially change over time as new Sources, Information, Evidence, and Analyses/Assessments are gathered and performed.


## Workflow
Begin by selecting a Subject to study. Then choose a Research Question for that Subject. Once determined, begin research. Research proceeds either through de novo searching (online or in-person) or by reviewing previously documented Information and Sources. De novo research involves searching for Sources that may be related to the Research Question. Research should be documented in a log: which database was used, what search terms, and which sources were found. Sources already existing in the database can be flagged for evaluation and new Sources can be added to 

## Organization
Binder = Surname: McNutt (1.5) McClean (1.5) Ebben (1.5) VanDyk (1.5) Tyrrell (1.5) Misc (2)
Separator = Family, Chronological back from source person: Robert C, Vivian G., 
