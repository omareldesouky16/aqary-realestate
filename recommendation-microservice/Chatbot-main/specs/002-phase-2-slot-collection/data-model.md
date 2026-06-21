# Data Model: Phase 2 Slot Collection

## SlotCollectionState

Represents the current progress of collecting search preferences for a chat session.

### Fields

- `required_slots`: Object containing `propertyType`, `location`, and `price` values, each with completion status.
- `budget_currency`: Currency used for the captured maximum budget; defaults to `EGP` when the buyer provides a numeric budget without explicit currency.
- `optional_slots`: Object containing `area`, `bedrooms`, `bathrooms`, and `features` values when provided.
- `missing_required_slots`: Ordered list of required slot names that are not yet complete.
- `next_question_slot`: The single required slot the assistant should ask for next, or `optional_preferences` after required slots are complete.
- `optional_collection_status`: `not_asked`, `asked`, `answered`, `declined`, or `skipped`.
- `search_ready`: Boolean indicating whether Phase 2 collection is complete enough for later search execution.
- `last_slot_prompted`: Most recent slot or grouped optional prompt asked by the assistant.
- `last_updated_turn`: Turn number or timestamp of the last slot-state update.

### Relationships

- Belongs to one authenticated chat session through the session state.
- Uses `SearchPreference` values for required and optional slots.
- Produces `ClarificationRequest` when a value is unclear or ambiguous.
- May enter `CashOnlyRedirectState` when the buyer asks about installments.

### Validation Rules

- `search_ready` MUST be false until `propertyType`, `location`, and `price` are complete.
- Numeric `price` values without explicit currency MUST be treated as EGP.
- `missing_required_slots` MUST preserve the order `propertyType`, `location`, `price`.
- `next_question_slot` MUST point to the first missing required slot until all required slots are complete.
- Optional collection MUST NOT block search readiness after the buyer declines optional preferences.
- A temporary interpretation failure MUST NOT clear existing slot values.

## SearchPreference

Represents one buyer-provided property-search preference.

### Fields

- `name`: One of `propertyType`, `location`, `price`, `area`, `bedrooms`, `bathrooms`, or `features`.
- `value`: The extracted buyer-provided value or values.
- `raw_text`: The original phrase that produced the value, when useful for clarification or later resolution.
- `source_turn`: The user turn where the preference was last explicitly provided.
- `required`: Boolean indicating whether the preference is required for search readiness.
- `status`: `complete`, `missing`, `unclear`, `ambiguous`, or `declined`.

### Validation Rules

- `propertyType`, `location`, and `price` are required.
- `area`, `bedrooms`, `bathrooms`, and `features` are optional.
- `price`, `area`, `bedrooms`, and `bathrooms` must be numeric when marked complete.
- `features` may contain multiple values.
- Payment method MUST NOT be represented as a search preference.
- New explicit values may replace previous values before search execution.

## RequiredSearchPreference

Specialization of `SearchPreference` for property type, location, and maximum budget.

### Fields

- `propertyType`: Desired property category, stored as best-effort extracted text until canonical resolution.
- `location`: Desired city, district, or neighborhood phrase, stored as best-effort extracted text until canonical resolution.
- `price`: Buyer's maximum budget.
- `currency`: Budget currency, defaulting to `EGP` when omitted by the buyer.

### State Rules

```text
missing
  -> complete       when a clear value is provided
  -> complete       when a numeric budget omits currency, using EGP
  -> unclear        when the value is not usable
  -> ambiguous      when multiple meanings are possible
  -> complete       after buyer clarifies an unclear or ambiguous value
```

## OptionalSearchPreference

Specialization of `SearchPreference` for area, bedrooms, bathrooms, and features.

### Fields

- `area`: Minimum area preference when provided.
- `bedrooms`: Bedroom preference when provided.
- `bathrooms`: Bathroom preference when provided.
- `features`: Desired feature names or phrases when provided.

### State Rules

```text
not_asked
  -> asked          after all required slots are complete
  -> answered       when one or more optional values are provided
  -> declined       when the buyer says optional preferences are not important
  -> skipped        when the buyer proceeds without optional preferences
```

## ClarificationRequest

Represents a targeted question for unclear or ambiguous slot values.

### Fields

- `slot_name`: The required or optional slot needing clarification.
- `reason`: `unclear`, `ambiguous`, `unsupported`, or `invalid_format`.
- `raw_text`: The buyer phrase that caused the clarification.
- `candidate_values`: Possible meanings when ambiguity is known.
- `prompted_at_turn`: Turn where the clarification was asked.
- `resolved`: Boolean indicating whether the buyer answered the clarification.

### Validation Rules

- Required slots with unresolved clarification requests MUST NOT be marked complete.
- Clarification prompts MUST be targeted to the unclear value, not a full restart of slot collection.
- Previously completed unrelated slots MUST remain intact during clarification.

## CashOnlyRedirectState

Represents interruption of slot collection when the buyer asks for unsupported payment terms.

### Fields

- `installment_requested`: True when the buyer asks about installment, down payment, or monthly payment.
- `redirect_prompted`: True after the buyer has been told searches are cash-only.
- `resume_slot`: The next missing non-payment slot to continue after the buyer accepts cash listings.

### State Rules

```text
normal slot collection
  -> cash-only redirect       when installment/down-payment/monthly-payment is requested
  -> normal slot collection   when buyer accepts cash listings
  -> unsupported path ends    when buyer declines cash listings
```

## Overall Slot Collection Transitions

```text
new or active search context
  -> collect propertyType
  -> collect location
  -> collect price
  -> ask grouped optional question
  -> optional answered OR optional declined OR optional skipped
  -> search_ready = true
```

```text
any slot collection state
  -> clarification requested for unclear required value
  -> buyer clarifies
  -> resume from first missing required slot
```

```text
any slot collection state
  -> cash-only redirect
  -> buyer accepts cash
  -> resume from next missing non-payment slot
```
