# Research: Phase 2.5 Location, Feature, and Property-Type Resolution

## Decision: Resolve canonical values in Laravel services before search readiness

**Rationale**: The constitution requires deterministic grounding before SQL, ranking, or persisted search state use extracted real estate preferences. Laravel services can validate LLM-extracted raw phrases against product data, aliases, and explicit confidence rules while keeping provider output untrusted.

**Alternatives considered**: Letting the LLM choose canonical IDs was rejected because it can hallucinate or silently choose the wrong value. Delaying resolution until search execution was rejected because Phase 2.5 must prevent false readiness and zero-result searches earlier in the flow.

## Decision: Store both raw phrases and canonical resolver outcomes

**Rationale**: Raw phrases support buyer-facing clarification and maintainer review. Canonical IDs/names support later search and ranking. Keeping both avoids losing the buyer's wording while preventing raw text from driving search behavior.

**Alternatives considered**: Storing only canonical values would make alias tuning and clarification harder. Storing only raw text would preserve ambiguity and keep later phases unsafe.

## Decision: Use managed aliases outside the chatbot flow

**Rationale**: The spec explicitly excludes buyer-facing or maintainer-facing alias UI. A managed data/configuration path lets maintainers add approved aliases while keeping Phase 2.5 focused on runtime resolution and review logging.

**Alternatives considered**: Building an admin UI was rejected by clarification. Hard-coding all aliases in prompts was rejected because coverage must improve after launch without conversation redesign.

## Decision: Limit ambiguity prompts to 3 candidates

**Rationale**: The feature clarification and FR-022 require no more than 3 candidates. A small candidate list keeps the chat prompt usable and avoids overwhelming buyers while still preventing incorrect guesses.

**Alternatives considered**: Showing all plausible candidates was rejected because it violates the requirement and makes chat replies noisy. Choosing the highest candidate automatically was rejected for required values with similar confidence.

## Decision: Optional feature ambiguity must not block search readiness

**Rationale**: Features improve relevance but are optional. Required location and property type must block readiness until resolved; unclear optional features can receive at most one concise clarification and then be ignored or skipped without losing other collected preferences.

**Alternatives considered**: Blocking readiness on every optional feature phrase was rejected because it would slow valid searches. Ignoring all feature ambiguity without a prompt was rejected because clear buyer intent should be recoverable when a short clarification is useful.

## Decision: Resolution review records minimize personal data

**Rationale**: Maintainers need unresolved/ambiguous phrase, preference type, candidates, and eventual selected value for tuning. They do not need full chat history or unrelated personal content, so review records should store a narrow text slice and metadata.

**Alternatives considered**: Storing entire messages in review queues was rejected for privacy. Not storing review data was rejected because SC-009 requires coverage improvement over time.

## Decision: Cash-only redirect remains higher priority than resolution

**Rationale**: The product constraint requires installment requests to redirect and never create payment slots. If payment intent appears while resolution is pending, the redirect path must preserve existing search state and avoid treating payment language as resolver input.

**Alternatives considered**: Resolving other values first was rejected because it could accidentally store unsupported payment preferences or confuse the buyer about cash-only support.
