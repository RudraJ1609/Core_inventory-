import { useQueryClient } from "@tanstack/react-query";
import {
  useListItems,
  useCreateItem,
  useUpdateItem,
  useDeleteItem,
  useListHistory,
  useGetDashboard,
  getListItemsQueryKey,
  getGetDashboardQueryKey,
  getListHistoryQueryKey,
} from "@workspace/api-client-react";

export function useInventoryQueries() {
  return {
    useDashboard: useGetDashboard,
    useItems: useListItems,
    useHistory: useListHistory,
  };
}

export function useInventoryMutations() {
  const queryClient = useQueryClient();

  const invalidateAll = () => {
    queryClient.invalidateQueries({ queryKey: getListItemsQueryKey() });
    queryClient.invalidateQueries({ queryKey: getGetDashboardQueryKey() });
    queryClient.invalidateQueries({ queryKey: getListHistoryQueryKey() });
  };

  const createItem = useCreateItem({
    mutation: { onSuccess: invalidateAll },
  });

  const updateItem = useUpdateItem({
    mutation: { onSuccess: invalidateAll },
  });

  const deleteItem = useDeleteItem({
    mutation: { onSuccess: invalidateAll },
  });

  return {
    createItem,
    updateItem,
    deleteItem,
  };
}
