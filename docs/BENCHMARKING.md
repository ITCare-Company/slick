
***
## <a name="benchmarking"> </a>Benchmarking & Performance Guidelines

This project is built on over a decade of addressing complex, real-world
performance constraints. This document defines a **repeatable, evidence-based
framework** for auditing and optimizing Slick within modern
[**Core Web Vitals (CWV)**](https://git.drupalcode.org/project/blazy/-/blob/3.0.x/docs/ARCHITECTURE.md) expectations.

We welcome **healthy skepticism**, critical audits, and alternative
implementations—**provided they are supported by verifiable data rather than
anecdotal observation**. Productive performance discussions require shared
grounding in technical context, reproducible methodology, and clearly defined
constraints. The guidelines below exist to ensure **fair, apples-to-apples
analysis** and to elevate discourse from opinion to contribution.

The web is a moving target. This is not a closed position, but an **open,
iterative process**. If you believe you have identified a regression or
architectural bottleneck—whether in modern metrics like **LCP**, **CLS** or even
overall system behavior—we invite you to validate it using the protocols below.
If the data holds, we are prepared to collaborate on refinement or correction.

In this space, **data is the bridge between a complaint and a contribution**.
Let us focus on the craft, serve the data, and raise the bar together.

---

### Standardized Benchmarking Protocols

Please regard this section as a technical foundation. If you are already fluent
with performance auditing fundamentals, feel free to skip ahead to the
contribution requirements.

For architectural context, see:
[Blazy & CWV Design Rationale](https://git.drupalcode.org/project/blazy/-/blob/3.0.x/docs/ARCHITECTURE.md#why-cwv)

To ensure informed and constructive evaluation, please follow these protocols:

#### Understand the Ecosystem

- Familiarize yourself with **Core Web Vitals** and the relevant configuration
  surfaces within **Blazy** and **Slick**, including Media formatter UIs.
- These tools are intentionally flexible; observed performance variance is
  often environmental or configurational rather than architectural.
- While standard bugs are addressed through regular maintenance, **this audit
  framework requires comprehensive benchmarking** to ensure technical accuracy
  and fairness.

---

#### Comparative Analysis

##### Scope

- Provide a **direct comparison** between:
  - Slick and alternative modules, or
  - Slick APIs and custom theme-level implementations.

##### Baseline

- Use code released **prior to 2025-10-20** as a historical reference point.
- Testing newer releases is encouraged, provided the exposure window and any
  post-patch improvements are clearly documented.

##### Professionalism

- If comparisons involve sensitive alternatives, you may provide names and test
  pages via private message to allow internal reproduction while keeping public
  discussion focused on technical findings.

---

#### Stress Testing (Required)

Because Drupal core already provides native lazy-loading for `IMG` and
`IFRAME`, meaningful audits must extend beyond trivial cases.

- Benchmarks **must include at least one complex media type**:
  **VIDEO**, **AUDIO**, or **HTML**.
- As the Blazy ecosystem is designed for mixed-media coordination:
  - Either ensure functional parity between alternatives, or
  - Isolate media types onto separate pages for controlled comparison.
- A **minimum 20-item sample** is required to establish a statistically
  meaningful baseline.
- Front-end systems should be evaluated under load; architectural behavior
  becomes visible only under meaningful stress.

---

#### Media Placement Awareness

- Benchmarks must explicitly distinguish between:
  - **Above-the-fold (LCP-critical)** assets
  - **Below-the-fold** deferred content
- Architectural intent differs between critical and non-critical media and must
  be evaluated accordingly.

---

#### Isolation Requirements

- Tests must be conducted in **Production mode** with strict, balanced
  isolation:
  - No library leaks
  - No global scope pollution
  - No ads or third-party noise
- If evaluating CLS relative to TTFB, temporarily (un-)install BigPipe until the
  rendering strategy is fully understood:
  [Have your cake and eat it too](https://git.drupalcode.org/project/blazy/-/blob/3.0.x/docs/OPTIMIZATION.md#cls).

Proper [isolation](https://git.drupalcode.org/project/blazy/-/blob/3.0.x/docs/OPTIMIZATION.md#cls) and [configuration](#optimization) enable accurate
evaluation.

---

#### Objective
Genuine, accountable, and technically rigorous corrections that prioritize
[project alignment with **Core Web Vitals**](https://git.drupalcode.org/project/blazy/-/blob/3.0.x/docs/ARCHITECTURE.md) and eliminate hindrances to
high-quality contributions.

---

### Path to Contribution

If you believe you have identified a genuine flaw, we welcome your report. To
respect community time and maintain project velocity, we ask for the following
due diligence:

#### 1. Define the Flaw

A qualifying flaw is a **measurable discrepancy** between two or more
benchmarked implementations.

Based on one publicly and three privately recorded benchmarks, historically
observed baselines indicate that **meaningful regressions** typically manifest
as:

- ~**1500% increase in page weight**, or
- ~**100% speed discrepancy**

These figures are **reference magnitudes**, not rhetorical thresholds or
anomalies, and scale with sample size and architectural complexity.

Minor library and asset size differences resulting from
**intentional modular features** (skins, media players, lightboxes, etc.) are
recognized as explicit trade-offs for advanced functionality and fall outside
core architectural regression analysis. We view these as essential efficiency
gains for site builders rather than architectural bloat or performance
regressions.

---

#### 2. Document the Setup

Provide comprehensive documentation, including:

- Formatter and UI screenshots
- Comparative configuration states
- Accessible test pages
- Lighthouse / GTmetrix / CWV reports

---

#### 3. Pro Tips for Accurate Audits:

- Use **Dropzone JS** for local file handling.
- Use a single unlimited **Media field** to easily switch formatters within
  Views blocks to ensure identical server-side burdens.
- To avoid measurement complications from nested field formatters, exclude
  Views-style sliders (Slick Views), Slick Paragraphs or Slick Vanilla in favor
  of direct **Slick Media** formatter implementations for now.
- Apply the identical **CWV** rules without reservation, including into hidden
  or nested formatters.

---

#### 4. Technical Rigor

High-effort submissions receive high-speed resolution.

Minimum requirements:

- **Objective Benchmarking:**

  Technical findings using "apples-to-apples" comparisons under identical CWV
  protocols and environmental parity.

- **Evidence over Anecdote:**

  Subjective observation and misconfiguration must not substitute for measured
  results. "Exploiting" self-inflicted bottlenecks—such as mocking performance
  while neglecting or refusing to enable caching or asset aggregation—is
  considered anecdotal, not evidence-based. Anecdotal lags are possible, but not
  hard evidence. While we appreciate and have given due credit with a sincere
  gratitude for a well-crafted "hilarious failure" and the comedy found in the
  architectural struggle, we now aim to move beyond surface-level tropes.

- **Resolved Issue Isolation:**

  Leverage the provided configuration and [Strategic Optimization Checklist](#optimization) below to ensure resolved issues remain isolated from the
  audits.

---

#### 5. What are the benefits for you?

**Credits and gratitude.** Every successful contribution or data-driven
disagreement that leads to a code correction is celebrated. We provide
[due credit](https://www.drupal.org/node/2232779/committers) to our
contributors, ensuring your expertise is recognized by the entire community.

---

### Note on Native Lazy-Loading & CWV

Native lazy-loading is valuable, but **one-size-fits-all implementations** do
not eliminate architectural differences under CWV scrutiny.

While Slick may appear marginally heavier at first glance—because the initial
visible asset is **intentionally not lazy-loaded to preserve LCP**—applying
identical CWV constraints across all components consistently reveals the true
performance characteristics, **as of this writing**. A deep understanding of
these subtleties—specifically regarding **hidden** and **nested** formatters—is
essential for any meaningful contribution.

This framework reflects long-standing benchmarking precedent. Continued
reliance on **legacy anecdotes** or **subjective critiques** may stall
ecosystem progress.

> *Unresolved technical assumptions hinder a healthy ecosystem.*

To move forward, we encourage a transition from repetitive discourse to
**reproducible, data-driven accountability**.

---

### Data-Driven Accountability

This project prioritizes architectural integrity and CWV compliance over the
path of least resistance.

All feedback is welcome when expressed as **accountable contribution**.

This challenge is **not validation-seeking nor confrontational**. We remain
open to the possibility that relevant factors have been overlooked, and humbly
invite engagement grounded in technical merit.

By **serving the data**, you help maintain a professional, high-performance
environment for the benefit of the wider Drupal community.


---

## <a name="optimization"> </a>Strategic Optimization Checklist

Proper configuration ensures the module works for you, not against you. Use this checklist to audit your implementation:

### 1. Essential UI Refinements

- **Optimized Mode:**

    Enable the **Optimized** checkbox in the Slick optionset to strip
    unnecessary bytes.

- **Production Clean-up:**

    Always **uninstall Slick UI** in production; configuration belongs in code
    or exported features.

### 2. Asset & Resource Management

- **CSS Lean-loading:**

    Disable the core `slick-theme.css` library if using custom icon fonts at
    `/admin/config/media/slick/ui`. Only if broken, copy any of its relevant
    rules into your own theme.

- **Lazyload HTML:**

    For third-party embeds (Instagram, Pinterest, etc.), enable
    **Lazyload HTML** in the Blazy UI. Offloading heavy third-party scripts
    prevents main-thread blocking and preserves host page performance.

- **Lazyload IFRAME:**

    Using the **Media Switcher** option with a static image preview is the
    primary defense against heavy third-party scripts. By intercepting iframe
    requests until user interaction, the main thread remains responsive during
    initial page load.

- **Global Performance:**

    Ensure Drupal’s core **CSS/JS aggregation** and caching are active at
    `/admin/config/development/performance`.

### 3. Media & Image Engineering

- **Prevent Layout Shift (CLS):**

    The **Aspect Ratio** is our primary defense against
    **Cumulative Layout Shift (CLS)**. By reserving space before media loads, we
    prevent container collapse and page jumps.

    - **Strategy:**

      Use image styles with a **"crop"** effect whenever possible.
      Select the **Aspect ratio** in the formatter UI and enable **Modern CSS aspect-ratio** in Blazy settings.

    - **Fluid Logic:**

      While native lazy loading handles basic shifts, Blazy’s
      **adaptive intelligence** provides robust fallbacks for legacy
      environments and fluid containers.

    - **The BigPipe + Blazy bridge:**

      See how to [have your cake and eat it too](https://git.drupalcode.org/project/blazy/-/blob/3.0.x/docs/OPTIMIZATION.md#cls).

- **Loading Priority:**

    Use **Preload** and **Loading Priority** options for "above-the-fold" assets
    to optimize **Largest Contentful Paint (LCP)**. Treat hero media as a
    priority, not an afterthought.

- **Responsive Standards:**

    Prioritize **Core Responsive Image** whenever storage permits. If storage is
    a constraint, utilize modern formats like **WebP** or **AVIF** to maintain
    visual fidelity at a fraction of the weight along with a versatile design.

### 4. Logic & Interaction Settings

- **Disable Autoplay/Infinite:**

  Unless strictly required, turn these off to prevent early downloads and
  expensive DOM reflows. Only reasonable for text marquees or trivial
  slideshows where performance is not a concern.
  **Autoplay** triggers downloads that can defeat lazy-loading, and **Infinite**
  loops often cause continuous, expensive DOM reflows including duplicate HTTP
  requests due to cloned slides.

- **Grid Strategy:**

  Use **HTML Formatter Grids** instead of JavaScript-based
  **Optionset Grids**. Server-side cached HTML is significantly more performant
  than generating complex DOM trees on-the-fly via JavaScript.

- **Scalability for Galleries:**

  For massive sets, use **Blazy Grid + Lightbox** (Colorbox, PhotoSwipe, etc.).
  This is objectively faster than a Slick-only implementation for static
  viewing, at least until we can make ajaxified Slick (3-4-hour community-funded
  efforts or sponsorships are welcome at
  [Slick Views](https://drupal.org/project/slick_views)).

### 5. Additional Optimization Settings & Automated Intelligence
While Blazy supports backward compatibility (BC) by default, you should optimize
for modern environments by leveraging both UI options and the module's internal
logic:

- **Native Lazyload:**

    Favor native browser lazy-loading to reduce main-thread execution by
    enabling **No JavaScript + polyfills** when targetting modern sites. This
    also minimizes "apples-to-oranges" benchmarking issues when comparing Slick
    against feature-limited alternatives.

- **Lean Markup:**

    Avoid "Divitis." Enable **Remove field/view wrapper CSS classes** and if
    provided, ensure **"Use theme field"** remains unchecked to keep the DOM
    tree shallow and fast.

- **Noscript Compatibility:**

    While `<noscript>` provides a fallback, it adds HTML weight. If your target
    audience is modern browsers or performance-critical, disable this fallback
    to shave off every possible byte.


- **Fine-Tuning:**

   * Audit your settings at `/admin/config/media/blazy`,
     `/admin/config/media/slick/ui`, and Media formatters, for more optimization
     options. The administrative UI is your cockpit for precision tuning.
   * Refer to [Blazy Optimization Checklist](https://git.drupalcode.org/project/blazy/-/blob/3.0.x/docs/OPTIMIZATION.md) for additional details.

---

## <a name="manifesto"> </a> Technical Manifesto: A Decade of Empirical Introspection

### The Era of Anecdotal Critique

In the first five years of this project, development encountered recurring
opposition framed as performance critique. Unfortunately, some assertions were
presented as definitive conclusions without accompanying data or reproducible
benchmarks—an approach that can obscure rather than clarify technical reality.

At the time, as a newcomer to module development while the project was still in
its staggering baby beta steps, I allowed for the possibility that these
perspectives were valid. However, professional rigor ultimately required
verification through objective measurement.

Not all feedback was anecdotal. Many helpful and highly skilled contributors
offered valuable guidance. I especially remember an excellent contribution from
the Coder maintainer, who kindly identified mistakes, explained the reasoning,
and even provided a fix. It was a compiler-related issue: `$this->$var` should
be `$this->{$var}`. That kind of contribution—precise, focused on the root
cause, educational, and constructive—was invaluable to me as a learner. They
knew the code was rough, yet still took the time to help.

#### The Benchmarking Reality

To resolve recurring questions, I conducted structured benchmarking using
**XHProf** and **GTMetrix**. The results—archived in a long-standing project
issue—showed a stark reality: some alternatives claimed to be leaner were
significantly heavier (up to **~1500%**) and measurably slower, with microsecs
higher memory overhead given just 17 images. The gap also increased with sample
size, including comparisons against the project in its beta form.

This revealed a consistent pattern: assumptions had been treated as conclusions
without validation. The lesson is straightforward—

**performance claims require data, not volume or controversy.**

We value simplicity (“KISS”) where it is appropriate. However, simplicity is not
a substitute for solving complex problems, and applying it indiscriminately can
lead to incorrect conclusions or measurable regressions.

#### The Cycle of Resistance

Given the discrepancy, benchmark context was added to the project home as a
reference point. This was not intended as a "**boast**", but as documentation—
a baseline for informed discussion. It served as a technical boundary: a pause
for reflection, or ready **counter-data** when discussions drifted away from
evidence.

It is natural for maintainers to be protective of work they built—especially in
its fragile early stages. Most creators understand that instinct.

To some readers, that may appear overly assertive or protective. Others who
review the issue history, documentation, or conduct independent benchmarking may
simply view it as pragmatic context, underscored by the caveat
"**for better or worse**" and a careful nod to Uncle Ben's responsibilities of
power. Perception varies.

Despite this, similar cycles continued: empirical data was often bypassed in
favor of recurring, unverified claims. This creates friction—not because of
disagreement, but because discussions restart without engaging existing
benchmark evidence or project home documentation.

It is worth noting:

strong claims can sometimes be perceived as **arrogance**.

That perception is understandable, particularly for readers unfamiliar with
the project’s early rough history. However, in engineering, the most productive
response is **counter-data**: reproducible evidence, clear benchmarks, and
actionable findings, rather than repetition of unsupported claims or persistent
mis-characterization of the work.

In good faith, and with the aim of breaking that cycle,
[**Peaceseeder**](#benchmarking) was established to help facilitate credible and
accountable **counter-data**. We hope it encourages those who have criticized
Slick over the years—or anyone with genuine concerns—to demonstrate their points
through measurable evidence that can benefit the project, its users, and the
contributors themselves through the credibility and recognition that come with
meaningful results under the
[**Benchmarking & Performance Guidelines**](#benchmarking).

We are not concerned by correction. Over time, this project has adapted to many
valid critiques and treated mistakes as lessons. Good-faith correction remains
welcome.

---

### Technical Accountability & Performance Discussions (Peaceseeder)

Performance discussions are valuable, but they are most productive when grounded
in reproducible evidence and project context.

To improve signal quality and reduce recurring ambiguity, this project adopts a
simple technical baseline for performance conversations.

This baseline is not intended to discourage critique. It exists to make critique
actionable, comparable, and technically useful.

#### Baseline Expectations

Performance claims are most useful when they include:

* Reproducible benchmarks or measurable observations.
* Relevant context (configuration, environment, dataset size, caching state,
  aggregation state, architectural constraints, ecosystem solutions,
  **Core Web Vitals**, etc.).
* Reference to existing project documentation, known caveats, or documented
  remedies where applicable.
* Minimal configuration details sufficient to allow independent verification.

When essential context is missing, it becomes difficult to distinguish between:

* project limitations,
* configuration or site building issues,
* environmental constraints,
* integration choices,
* or genuine defects requiring investigation.

The goal is not to raise barriers to participation.

The goal is to reduce circular discussion and improve the quality of technical
analysis.

The purpose of documenting these expectations is not to discourage critique,
but to encourage discussions that are technically rigorous, actionable, and
useful to the broader community.

If performance concerns are identified, contributors are sincerely encouraged to
provide benchmarks, reproducible conditions, or implementation evidence so
findings can be evaluated fairly and independently.

#### Examples of Low-Signal Performance Reports

The following patterns are difficult to evaluate without supporting data or
context:

1. Reporting severe performance issues without benchmark data, environment
   details, or reproducible steps.

2. Evaluating behavior under intentionally atypical conditions (for example,
   disabled caching or disabled aggregation) without clearly identifying those
   conditions.

3. Declaring a feature "heavy" or "slow" without comparative measurements
   against equivalent alternatives or comparable workloads.

4. Attributing layout instability, loading behavior, or rendering costs
   exclusively to this project when multiple site-level factors may contribute.

5. Drawing conclusions from incomplete configuration or without testing
   documented configuration guidance, caveats, or performance recommendations.

6. Proposing architectural changes without accompanying benchmarks,
   proof-of-concept implementations, or measurable improvement criteria.

These examples are not dismissed automatically. They simply require additional
technical grounding to become actionable.

#### Examples of Productive Critiques

The following are always welcomed:

* Reproducible bug reports.
* Benchmark-backed performance analysis.
* Comparative measurements.
* Configuration-specific observations.
* Architectural critique supported by data or implementation evidence.
* Counterexamples demonstrating measurable regressions.
* Improvements, patches, experiments, or independently verifiable
  investigations.

Constructive, data-driven and solution-oriented disagreement is valuable.

Historically, many improvements in this project originated from strong technical
criticism backed by testing, experimentation, or field observations.

Incorrect observations are also normal in engineering work. Everyone gets things
wrong occasionally, including maintainers. What matters is whether discussion
converges toward clearer understanding and better evidence.

---

### The Science of the Medium

From an artistic perspective, structural integrity is governed by science. We
understand how **Zinc White/PW4 (ZnO)** forms metal soaps that can lead to
delamination (as documented by Marion Mecklenburg and Charles Tumosa); how
environmental factors degrade materials over time; or the fascinating, albeit
toxic, history of **Lead White** from ancient cosmetics and paint to its modern
reproduction using ancient Dutch stack method and its European ban.

Technical systems behave similarly.

When the medium—code or communication—is compromised by weak assumptions or lack
of rigor, degradation follows. The focus here is:

**to prioritize measurable, testable understanding over unsupported claims.**

---

### Project Scope & Context

Slick has evolved over many years across varied deployment environments,
workflows, and performance expectations.

The project documentation includes known caveats, troubleshooting guidance, and
performance recommendations intended to help users operate within the tool’s
intended scope.

The system has been validated across legacy environments, including older
browser support, down to IE6–7, specific for Slick 7.2, with minor unpublished
adjustments. Modern Slick may slightly vary since Blazy 2.6 was only tested
against IE9, and its 3.x may likely abandon IE families, or require polyfills.

No system is perfect. However, repeatable performance data confirms the
project’s practical utility; and it continues to meet expectations under modern
**Core Web Vitals** evaluations when measured properly and consistently. Like
any software system, Slick is not universally optimal for every use case. Users
should evaluate whether it fits their requirements, constraints, and priorities.

If there are areas for improvement, they are welcome—provided they are
demonstrated clearly and tested rigorously. Contributions that clarify,
benchmark, or improve the system are valued.

---

### Guiding Principle

This project is offered as-is, with a consistent technical principle:

**Technical decisions should be guided by observable evidence rather than
unsupported assumptions.**

If performance concerns exist, they are welcome topics for investigation.

The preferred path is straightforward:

* describe the environment,
* provide reproducible measurements,
* share benchmarks or comparative data,
* identify the observed behavior clearly enough for others to validate
  independently.
* and meet the [**Technical Accountability Challenge**](#benchmarking) for more
  detailed steps in a credible and actionable manner. It may take a pro site
  builder around 15 minutes, or end users an hour or less. No coding skill is
  required, just **site building** and proper understanding of the requirements.

That approach helps maintainers, contributors, and users spend limited time on
issues that can be analyzed, reproduced, and improved.

Slick is just a tool, and must be treated so by reading its manual before
use or unsupported claims. As a tool, "**avoidable configuration pitfalls**" due
to improper use have been socialized for many years under **TROUBLESHOOTINGS**
and **PERFORMANCE TIPS** sections with explicit **for better or worse** caveats,
including a benchmark link in the project home as an example for independent
audits before repeating unsupported claims. Since 2025, the link was removed to
encourage independent and objective benchmarks without being influenced by
historical achievements which serve more as a baseline context now.

While there is no longer capacity to engage endlessly with unsupported claims,
collaboration remains welcome when it is data-driven, solution-oriented, and
focused on advancing the project for the broader community, and only when time
permits.

[**Technical Accountability Challenge**](#benchmarking) is now formally taking
over our job to keep unsupported claims entertained in the most professional,
beneficial and actionable manner.
