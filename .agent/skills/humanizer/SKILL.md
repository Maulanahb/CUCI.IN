---
name: humanizer
description: Remove signs of AI-generated writing from text to make it sound natural, clear, and human. Use when the user asks to humanize text, remove AI tone or AIisms, edit or rewrite drafts to sound less robotic or more natural, or polish articles, essays, emails, social media posts, and documentation.
---
# Humanizer

Identify and remove patterns typical of AI-generated writing so text reads as if written by a thoughtful human. Stripping bad words is only half the job - replace hollow inflation and robotic symmetry with concrete details, varied sentence rhythm, and authentic voice.

## When to Use

  - The user asks to "humanize" text, make it sound natural, or remove AI tone.
  - Editing, rewriting, or polishing drafts (essays, blog posts, copywriting, documentation, emails, or social posts) to eliminate robotic phrasing.
  - Reviewing written content for signs of LLM generation based on Wikipedia's "Signs of AI writing" guidelines.

## Steps

### 1. Scan for AI Writing Patterns

Read the input text and identify occurrences across the four core pattern groups below:

#### Content Patterns

  - **Undue emphasis on significance and legacy:** Cut phrases like "stands as a testament", "plays a vital role", "underscores its importance", "leaves an indelible mark", "pivotal moment", or "evolving landscape". State what happened and let the reader judge its importance.
  - **Superficial analyses with trailing -ing phrases:** Remove tacked-on participial clauses at the end of sentences such as "highlighting...", "underscoring...", "symbolizing...", "reflecting...", "fostering...", "showcasing...", or "ensuring...". If the clause adds no factual information, delete it; if it matters, make it a direct statement.
  - **Promotional and advertisement-like language:** Strip puffery such as "nestled in the heart of", "vibrant", "rich cultural tapestry", "breathtaking", "groundbreaking", "stunning", "renowned", or "must-visit". Replace adjectives with verifiable specifics.
  - **Vague attributions and weasel words:** Replace "Experts argue", "Industry reports suggest", "Observers have cited", or "Critics note" with specific sources, or state the point directly without fake citations.
  - **Formulaic "Challenges and Future Prospects" sections:** Avoid ending sections with generic templates like "Despite its success, X faces several challenges..." or "Looking ahead, the future of X remains bright."
  - **Didactic or moralizing conclusions:** Cut preachy wrap-ups starting with "In conclusion", "Overall", "Ultimately, this reminds us that...", or "In summary". End when the last substantive point is made.

#### Language and Grammar Patterns

  - **Overused AI vocabulary:** Watch for high-frequency LLM crutches and replace them with plain words:
      - English crutches: `additionally`, `furthermore`, `moreover`, `delve`, `tapestry`, `testament`, `underscore`, `showcase`, `foster`, `pivotal`, `crucial`, `intricate`, `multifaceted`, `meticulous`, `garner`, `interplay`, `enduring`, `enhance`, `align with`, `resonate`, `landscape`, `realm`, `beacon`.
      - Indonesian crutches: `lebih lanjut`, `selain itu` (at the start of every paragraph), `menyelami`, `lanskap yang terus berkembang`, `peran krusial`, `bukti nyata`, `tak dapat dipungkiri`, `di era digital yang serba cepat ini`, `secara keseluruhan`, `kesimpulannya`, `bukan sekadar X, melainkan Y`.
  - **Copula avoidance:** Stop replacing simple "is", "are", or "has" with "serves as", "stands as", "marks", "represents", "boasts", "features", or "offers". Write "The gallery has four rooms" instead of "The gallery boasts four distinct exhibition spaces."
  - **Negative parallelisms:** Cut formulaic contrasts like "It's not just X, it's Y", "Not only does it X, but it also Y", or "More than just a tool, it is a partner." State what the subject actually is.
  - **Rule of three:** Break up compulsive triads of adjectives, nouns, or clauses (for example, "driving efficiency, innovation, and growth" or "fast, reliable, and secure"). Use one strong word or the exact number of items that actually matter.
  - **Elegant variation (synonym cycling):** Do not cycle through awkward synonyms just to avoid repeating a noun (calling the same subject "the application", "the platform", "the digital solution", and "the ecosystem" in four consecutive sentences). Repeat the clear noun or use a pronoun.
  - **False ranges:** Remove "from X to Y" constructions where the endpoints do not form a meaningful spectrum (for example, "from solo developers to global enterprises").

#### Style and Formatting Patterns

  - **Em-dash overuse:** AI overuses dashes for dramatic pauses and parenthetical asides. Replace most of them with commas, periods, or parentheses.
  - **Mechanical bolding and inline-header lists:** Avoid turning every paragraph into a bulleted list of `**Bold Header:** Rest of sentence.` Use flowing prose when ideas connect, and reserve lists for genuinely discrete items.
  - **Title Case in headings:** Use normal sentence case for headings unless the user's style guide requires Title Case.
  - **Decorative emojis:** Strip emojis from headings and bullet points unless the user explicitly asked for casual social formatting.

#### Communication Artifacts and Filler

  - **Chatbot preambles and sign-offs:** Never include "Here is the rewritten version:", "Certainly!", "I hope this helps!", or "Let me know if you want any adjustments!" when delivering the final text.
  - **Filler phrases:** Trim wordy padding:
      - "In order to" -> "To"
      - "Due to the fact that" -> "Because"
      - "At this point in time" -> "Now"
      - "It is important to note that" -> Delete and state the point directly.
      - "Has the ability to" -> "Can"
  - **Excessive hedging:** Replace "could potentially possibly be seen as having an impact on" with direct verbs.

### 2. Inject Natural Rhythm and Voice

Removing AI patterns is not enough if the resulting text feels flat and sterile. Apply these principles while rewriting:

  - **Vary sentence length:** Mix short, direct sentences (under 8 words) with medium and longer sentences. Read the paragraph aloud mentally to check if every sentence has the same cadence.
  - **Prefer concrete specifics over abstract summaries:** Keep every real metric, date, name, and example from the original text.
  - **Allow a real point of view:** Human writing commits to its claims instead of balancing every sentence with "On one hand... on the other hand..." unless genuine nuance is required.
  - **Match the target language and register:** If the input is in Indonesian, use natural Indonesian flow (avoiding stiff literal translations of English idioms like "mengambil langkah mundur" or "di penghujung hari"). If casual, allow natural contractions and conversational transitions.

### 3. Self-Audit Before Returning Output

Before finalizing the rewritten text, run a quick verification pass:

1.  Ask: "What still makes this sound AI-generated?"
2.  Check for any surviving "-ing" tail clauses, "not just X, but Y" structures, or clustered buzzwords.
3.  Fix any remaining stiffness and present the clean version.

## Gotchas

  - **Do not strip factual accuracy or technical terms:** Domain-specific terminology is not AI fluff. Keep technical terms intact while simplifying the connective tissue around them.
  - **Do not over-casualize formal writing:** Humanizing an academic paper or executive memo means making it clear, direct, and grounded - not adding slang or jokes. Match the register of the user's document.
  - **Do not alter the core meaning:** Preserve all facts, constraints, and arguments from the original draft unless the user asks you to shorten or restructure the piece.