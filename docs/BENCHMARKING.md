
***
## <a name="benchmarking"> </a>Benchmarking & Performance Guidelines

This project is built on over a decade of addressing complex, real-world
performance constraints. This document defines a **repeatable, evidence-based
framework** for auditing and optimizing Slick within modern
**Core Web Vitals (CWV)** expectations.

We welcome **healthy skepticism**, critical audits, and alternative
implementations—**provided they are supported by verifiable data rather than
anecdotal observation**. Productive performance discussions require shared
grounding in technical context, reproducible methodology, and clearly defined
constraints. The guidelines below exist to ensure **fair, apples-to-apples
analysis** and to elevate discourse from opinion to contribution.

The web is a moving target. This is not a closed position, but an **open,
iterative process**. If you believe you have identified a regression or
architectural bottleneck—whether in modern metrics like **LCP**, or even
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

Proper isolation enables accurate judgment.

---

#### Objective
Genuine, accountable, and technically rigorous corrections that prioritize
project alignment with **Core Web Vitals** and eliminate hindrances to
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

Minor library and asset size differences resulting from **intentional modular features**
(skins, media players, lightboxes, etc.) are recognized as explicit trade-offs
for advanced functionality and fall outside core architectural regression
analysis. We view these as essential efficiency gains for site builders rather
than architectural bloat or performance regressions.

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

---

#### 3. Technical Rigor

High-effort submissions receive high-speed resolution.

Minimum requirements:

- **Objective Benchmarking:**

  Technical findings using "apples-to-apples" comparisons under identical CWV protocols and environmental parity.

- **Evidence over Anecdote:**

  Subjective observation and misconfiguration must not substitute for measured
  results. "Exploiting" self-inflicted bottlenecks—such as mocking performance
  while neglecting or refusing to enable caching or asset aggregation—is
  considered anecdotal, not evidence-based. Anecdotal lags are possible, but not
  hard evidence. While we appreciate and have given due credit with a sincere gratitude for a well-crafted "hilarious failure" and the comedy found in the
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
performance characteristics, as of this writing. A deep understanding of these subtleties—specifically regarding **hidden** and **nested** formatters—is
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
    primary defense against heavy third-party scripts. By intercepting iframe requests until user interaction, the main thread remains responsive during initial page load.

- **Global Performance:**

    Ensure Drupal’s core **CSS/JS aggregation** and caching are active at
    `/admin/config/development/performance`.

### 3. Media & Image Engineering

- **Prevent Layout Shift (CLS):**

    The **Aspect Ratio** is our primary defense against
    **Cumulative Layout Shift (CLS)**. By reserving space before media loads, we
    prevent container collapse and page jumps.

    - **Strategy:** Use image styles with a **"crop"** effect whenever possible.
      Select the **Aspect ratio** in the formatter UI and enable **Modern CSS aspect-ratio** in Blazy settings.

    - **Fluid Logic:** While native lazy loading handles basic shifts, Blazy’s
      **adaptive intelligence** provides robust fallbacks for legacy
      environments and fluid containers.

    - **The BigPipe + Blazy bridge:** See how to [have your cake and eat it too](https://git.drupalcode.org/project/blazy/-/blob/3.0.x/docs/OPTIMIZATION.md#cls).

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
  viewing, at least until we can make ajaxified Slick (3-4-hour community-funded efforts or sponsorships are welcome at [Slick Views](https://drupal.org/project/slick_views)).

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

    Avoid "Divitis." Enable **Remove field/view wrapper CSS classes** and if provided, ensure **"Use theme field"** remains unchecked to keep the DOM
    tree shallow and fast.

- **Noscript Compatibility:**

    While `<noscript>` provides a fallback, it adds HTML weight. If your target audience is modern browsers or performance-critical, disable this fallback
    to shave off every possible byte.


- **Fine-Tuning:**

   * Audit your settings at `/admin/config/media/blazy`,
     `/admin/config/media/slick/ui`, and Media formatters, for more optimization
     options. The administrative UI is your cockpit for precision tuning.
   * Refer to [Blazy Optimization Checklist](https://git.drupalcode.org/project/blazy/-/blob/3.0.x/docs/OPTIMIZATION.md) for additional details.
