import { Issue } from './Issue';

export interface RelationshipType {
  id: number;
  name: string;
  label: string;
}

export interface Relationship {
  id: number;
  type: RelationshipType;
  issue: Issue;
}
