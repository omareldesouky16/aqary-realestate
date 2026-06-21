export type ChatIntent =
  | 'search_property'
  | 'show_more_results'
  | 'property_details'
  | 'show_property_photos'
  | 'seller_contact'
  | 'complaint'
  | 'installment_redirect'
  | 'chitchat'
  | 'unclear'
  | 'system_error';

export interface ChatRequest {
  session_id: string;
  message: string;
  context_property_id?: number | null;
}

export interface PropertyReference {
  id: number;
  title: string;
  url: string;
  position?: number;
}

export interface ChatResponse {
  reply: string;
  intent: ChatIntent;
  isComplaint: boolean;
  needsCheckIn?: boolean;
  complaint_case?: ComplaintCase | null;
  installment_redirect: boolean;
  awaiting_slots: string[];
  slot_collection: SlotCollectionState;
  resolution: ResolutionState | null;
  resolved_property_id: number | null;
  resolved_by?: 'position' | 'title_match' | 'id_explicit' | 'page_context' | null;
  user_reference?: string | null;
  property_reference?: PropertyReferenceState | null;
  property_detail?: PropertyDetail | null;
  property_gallery?: PropertyGallery | null;
  seller_contact?: SellerContact | null;
  properties: SearchResultItem[];
  search?: SearchState;
  show_image_offer: boolean;
  has_more: boolean;
  min_price_fallback: number | null;
  session_id: string;
  fallback?: boolean;
}

export interface SlotValue {
  value: string | number | string[] | null;
  raw_text?: string | null;
  currency?: 'EGP' | null;
  status: 'complete' | 'missing' | 'unclear' | 'ambiguous' | 'declined';
}

export interface ClarificationRequest {
  slot_name: 'propertyType' | 'location' | 'price' | 'area' | 'bedrooms' | 'bathrooms' | 'features';
  reason: 'unclear' | 'ambiguous' | 'unsupported' | 'invalid_format';
  raw_text?: string | null;
  candidate_values?: string[];
}

export interface SlotCollectionState {
  required_slots: {
    propertyType: SlotValue;
    location: SlotValue;
    price: SlotValue;
  };
  optional_slots: {
    area: SlotValue;
    bedrooms: SlotValue;
    bathrooms: SlotValue;
    features: SlotValue;
  };
  missing_required_slots: Array<'propertyType' | 'location' | 'price'>;
  next_question_slot: 'propertyType' | 'location' | 'price' | 'optional_preferences' | null;
  optional_collection_status: 'not_asked' | 'asked' | 'answered' | 'declined' | 'skipped';
  search_ready: boolean;
  budget_currency: 'EGP' | null;
  clarification: ClarificationRequest | null;
}

export interface ResolutionCandidate {
  canonical_id: number;
  canonical_name: string;
  preference_type: 'location' | 'propertyType' | 'features';
  match_reason?: 'exact' | 'alias' | 'synonym' | 'similarity' | 'translation' | null;
  display_order: number;
}

export interface ResolutionOutcome {
  preference_type: 'location' | 'propertyType' | 'features';
  status: 'resolved' | 'ambiguous' | 'unresolved';
  raw_text?: string | null;
  canonical_id?: number | null;
  canonical_name?: string | null;
  resolved_by?: 'exact' | 'alias' | 'synonym' | 'normalization' | 'clarification' | null;
  candidates: ResolutionCandidate[];
  optional_blocking: boolean;
}

export interface ResolutionClarification {
  preference_type: 'location' | 'propertyType' | 'features';
  reason: 'ambiguous' | 'unresolved' | 'unsupported';
  raw_text?: string | null;
  candidates: ResolutionCandidate[];
}

export interface ResolutionState {
  outcomes: {
    location?: ResolutionOutcome | null;
    propertyType?: ResolutionOutcome | null;
    features?: ResolutionOutcome[];
  };
  pending_clarification: ResolutionClarification | null;
  review_item_ids: number[];
}

export interface ChatMessage {
  role: 'user' | 'assistant';
  text: string;
  response?: ChatResponse;
}

export type ComplaintStage = 'check_in' | 'awaiting_issue' | 'awaiting_phone' | 'invalid_phone_retry' | 'saved' | 'declined';

export interface ComplaintEvent {
  type: string;
  stage: string;
  message?: string | null;
  created_at: string;
  metadata?: Record<string, unknown>;
}

export interface ComplaintCase {
  status: 'active' | 'saved' | 'declined' | 'fallback_pending';
  stage: ComplaintStage;
  issue_summary?: string | null;
  issue_language?: string | null;
  phone_status: 'none' | 'pending' | 'valid' | 'invalid' | 'declined';
  follow_up_phone?: string | null;
  follow_up_phone_normalized?: string | null;
  follow_up_phone_attempts: number;
  reviewable: boolean;
  events: ComplaintEvent[];
}

export interface PropertyReferenceCandidate {
  position: number;
  property_id: number;
  title: string;
}

export interface PropertyReferenceState {
  status: 'resolved' | 'ambiguous' | 'missing' | 'stale' | 'unresolved';
  reference_type?: 'position' | 'title' | 'context' | 'explicit_id' | null;
  candidates: PropertyReferenceCandidate[];
  clarification_prompt?: string | null;
}

export interface PropertyDetail {
  id: number;
  title: string;
  url: string;
  price?: number | null;
  area?: number | null;
  bedrooms?: number | null;
  bathrooms?: number | null;
  furnished_status?: string | null;
  location?: string | null;
  floor_details?: string | null;
  map_available?: boolean;
  features?: string[];
  missing_fields: string[];
}

export interface PropertyGalleryImage {
  image_url: string;
  display_order: number;
  alt_text?: string | null;
}

export interface PropertyGallery {
  property_id: number;
  has_images: boolean;
  images: PropertyGalleryImage[];
}

export interface SellerContact {
  property_id: number;
  contact_available: boolean;
  phone?: string | null;
  withheld_reason?: 'not_explicit' | 'ambiguous_property' | 'inactive_property' | 'missing_contact' | 'unauthorized' | null;
}

export interface RankingScore {
  total_score: number;
  price_score?: number;
  area_score?: number;
  bedroom_score?: number;
  bathroom_score?: number;
  feature_score?: number;
  promotion_boost?: number;
  matched_feature_names?: string[];
}

// Phase 3 search result payloads contain only safe summary facts for the current visible page.
// Seller phone/contact fields and full listing records must never be added here.
export interface SearchResultItem extends PropertyReference {
  position: number;
  rank_position: number | null;
  price?: number | null;
  area?: number | null;
  bedrooms?: number | null;
  bathrooms?: number | null;
  furnished_status?: string | null;
  location?: string | null;
  cover_image_url?: string | null;
  has_cover_image?: boolean;
  matched_features?: string[];
  score?: RankingScore | null;
}

export interface BudgetFallback {
  minimum_available_price: number;
  scope_location: string;
  scope_property_type: string;
  stated_max_budget?: number | null;
  available_listing_count_in_scope?: number | null;
}

export interface SearchState {
  status: 'not_ready' | 'results' | 'no_results' | 'budget_fallback' | 'exhausted' | 'fallback';
  search_id?: string | null;
  result_count?: number;
  shown_count?: number;
  page_size?: number;
  has_more?: boolean;
  visible_reference_map?: Array<{ position: number; listing_id: number }>;
  min_price_fallback?: number | null;
  budget_fallback?: BudgetFallback | null;
}
